[TOC]

---

# Hubot と Let's Chat を連携させる

- 写経元
  - https://qiita.com/na1234/items/7098d638e88ff922dd2e

---

## Let's Chat の bot 用のアカウントが設定された状態のDocker Image を作る

- **<u>Docker Image は既に出来ているので使うだけの場合はこの手順は飛ばしてOK</u>**

- docker-compose.yml を書く

  - ```yaml
    app:
      image: sdelements/lets-chat
      links:
        - mongo
      ports:
        - 8080:8080
        - 5222:5222

    mongo:
      image: mongo:latest
    ```

- docker-compose up する

  - ```shell
    $ docker-compose up	
    ```

- http://localhost:8080 にアクセスする

  - ![Login · Let's Chat 2017-11-26 11-12-05](https://github.com/ma1979/sutra/raw/master/20171126_Hubot-LetsChat/cap/Login%20%C2%B7%20Let's%20Chat%202017-11-26%2011-12-05.png)

- bot 用のアカウントを作成する

  - ![Login · Let's Chat 2017-11-26 11-48-35](https://raw.githubusercontent.com/ma1979/sutra/master/20171126_Hubot-LetsChat/cap/Login%20%C2%B7%20Let's%20Chat%202017-11-26%2011-48-35.png)
  - とりあえずこんな感じで
    - ![Login · Let's Chat 2017-11-26 11-51-46](https://github.com/ma1979/sutra/raw/master/20171126_Hubot-LetsChat/cap/Login%20%C2%B7%20Let's%20Chat%202017-11-26%2011-51-46.png)

- bot 用アカウントでログインする

- 同じような感じで bot と対話テストするためのユーザを設定する。 hoge で設定した。

- テスト用のルームを作る

  - ![Rooms · Let's Chat 2017-11-26 11-57-58](https://github.com/ma1979/sutra/raw/master/20171126_Hubot-LetsChat/cap/Rooms%20%C2%B7%20Let's%20Chat%202017-11-26%2011-57-58.png)
  - こんな感じで
    - ![Rooms · Let's Chat 2017-11-26 11-59-55](https://github.com/ma1979/sutra/raw/master/20171126_Hubot-LetsChat/cap/Rooms%20%C2%B7%20Let's%20Chat%202017-11-26%2011-59-55.png)

- bot で token を発行する

  - ![token](https://github.com/ma1979/sutra/raw/master/20171126_Hubot-LetsChat/cap/Rooms%20%C2%B7%20Let's%20Chat%202017-11-26%2016-14-24.png)

- bot 用のアカウントとテスト用のルームがどういう形式で mongoDB に永続化されているかを確認する

  - docker exec でコンテナに入る

    - ```shell
      $ docker exec -it d44336c25626 bash
      ```

  - mongoDB に接続する

    - ```shell
      $ mongo
      ```

  -  database を確認する

    - ```
      > show dbs
      admin     0.000GB
      letschat  0.000GB
      local     0.000GB
      ```

  - letschat database に切り替える

    - ```
      > use letschat
      switched to db letschat
      ```

  - collection (RDB で言うところの Table )を確認する

    - ```
      > show collections
      messages
      rooms
      sessions
      usermessages
      users
      ```

  - Dockerfile に書くべき collection がどれかを確認する

    - ```
      > db.messages.find().count()
      0
      > db.rooms.find().count()
      1
      > db.sessions.find().count()
      1
      > db.usermessages.find().count()
      0
      > db.users.find().count()
      1
      ```

      - sessions はログインセッションが入っていると思うので rooms と users が対象みたい

  - と確認しつつも、mongodump でバックアップを取る

    - ```shell
      $ mongodump --archive=/tmp/letschat-hubot-mongo-initial.gz --gzip --db letschat
      2017-11-26T11:48:13.594+0000    writing letschat.users to archive '/tmp/letschat-hubot-mongo-initial.gz'
      2017-11-26T11:48:13.594+0000    writing letschat.rooms to archive '/tmp/letschat-hubot-mongo-initial.gz'
      2017-11-26T11:48:13.618+0000    writing letschat.messages to archive '/tmp/letschat-hubot-mongo-initial.gz'
      2017-11-26T11:48:13.626+0000    writing letschat.sessions to archive '/tmp/letschat-hubot-mongo-initial.gz'
      2017-11-26T11:48:13.644+0000    done dumping letschat.users (2 documents)
      2017-11-26T11:48:13.644+0000    writing letschat.usermessages to archive '/tmp/letschat-hubot-mongo-initial.gz'
      2017-11-26T11:48:13.647+0000    done dumping letschat.messages (0 documents)
      2017-11-26T11:48:13.649+0000    done dumping letschat.sessions (0 documents)
      2017-11-26T11:48:13.652+0000    done dumping letschat.usermessages (0 documents)
      2017-11-26T11:48:13.653+0000    done dumping letschat.rooms (1 document)

      ```

  - これを GitHub に push する

    - コンテナからdumpファイルを取り出す

      - ```shell
        $ docker cp letschatcompose_mongo_1:/tmp/letschat-hubot-mongo-initial.gz .
        ```

      - GitHub に push

  - mongoDB のコンテナに dump ファイルを import したイメージを作成する

    - Dockerfile を書く

      - ```dockerfile
        FROM mongo:latest
        MAINTAINER ma1979
        RUN apt-get update && apt-get install -y wget
        RUN cd /tmp && wget https://github.com/ma1979/letschat-hubot/raw/master/letschat-hubot-mongo-initial.gz
        RUN echo "#!/bin/bash" > /tmp/restore.sh
        RUN echo "sleep 5 && mongorestore --gzip --archive=/tmp/letschat-hubot-mongo-initial.gz --db letschat & " >> /tmp/restore.sh
        RUN echo "docker-entrypoint.sh mongod" >> /tmp/restore.sh
        RUN chmod 777 /tmp/restore.sh
        CMD sh /tmp/restore.sh
        ```

      - いろいろ試行錯誤したが、起動時にイニシャルデータを流す方法はこれしか分からなかった

        - https://stackoverflow.com/questions/39282957/mongorestore-in-a-dockerfile
          - foreground で mongod が起動するまで sleep で待って、mongod が起動したら mongorestore する

    - docker build する

      - ```shell
        $ docker build -t ma1979/letschat-hubot-mongo .
        ```

    - docker push する

      - ```shell
        $ docker push ma1979/letschat-hubot-mongo
        ```

      - ![ma1979 - Docker Hub 2017-11-26 12-11-28](https://github.com/ma1979/sutra/raw/master/20171126_Hubot-LetsChat/cap/ma1979%20-%20Docker%20Hub%202017-11-26%2012-11-28.png)


### おまけ

- mongoDBの基本的な操作方法
  - https://qiita.com/yuji0602/items/c55e2cb75376fd565b4e
    - スキーマレスなのでdrop＆createが簡単

---

## Let's Chat 用アダプタが設定された状態の Hubot の Docker Image を作る

- **<u>Docker Image は既に出来ているので使うだけの場合はこの手順は飛ばしてOK</u>**

- https://github.com/ma1979/sutra/blob/master/20171126_Hubot/Hubot%20%E7%92%B0%E5%A2%83%E6%A7%8B%E7%AF%89.md の slack を let's chat に読み替えるだけ

  - Dockerfile を書く

    - ```dockerfile
      FROM node
      MAINTAINER ma1979

      RUN npm install -g yo generator-hubot
      RUN npm list -g yo generator-hubot
      RUN useradd bot
      RUN mkdir /home/bot && chown bot.bot /home/bot

      USER bot
      WORKDIR /home/bot
      RUN  yo hubot --owner "ma1979" --name "bot" --description "Hubot image" --adapter lets-chat

      RUN echo "#!/bin/bash" > /home/bot/run_hubot.sh
      RUN echo "bin/hubot --adapter lets-chat" >> /home/bot/run_hubot.sh

      RUN chmod 777 /home/bot/run_hubot.sh

      CMD cd /home/bot; sh run_hubot.sh
      ```

  - docker build する

    - ```shell
      $ docker build -t ma1979/letschat-hubot .
      ```

  - docker push する

    - ```shell
      $ docker push ma1979/letschat-hubot
      ```

  - hubot だけ使う場合はこの Image に対して docker run するときに各種環境変数を渡すとよいと思われ



---

## bot 用アカウントが設定された状態の Let's Chat とそこに連携するよう設定された状態の Hubot を立ち上げる

- push した Image を使って docker-compose するように docker-compose.yml を書き直す

  - ```yaml
    version: '2'
    services:
      letschat:
        image: sdelements/lets-chat
        ports:
          - 8080:8080
          - 5222:5222
        expose: 
          - 8080

      mongo:
          image: ma1979/letschat-hubot-mongo

      hubot:
        image: ma1979/letschat-hubot
        environment:
          HUBOT_LCB_TOKEN: NWExYTQ4Nzg1MWFmZWYwMDBlYjMzYjNhOjk2YmJjYzgyMTNiNGRkMjhhZDdiZTg0MmRiMmZmNGVmOGNlNjQwZWJlNzIxY2ZmNg==
          HUBOT_LCB_ROOMS: 5a1a489951afef000eb33b3b
          HUBOT_LCB_PROTOCOL: http
          HUBOT_LCB_HOSTNAME: letschat
          HUBOT_LCB_PORT: 8080
          PORT: 8082
        ports:
          - 8082:8082
        expose:
          - 8082
    ```

  - コンテナ間通信をするときは version 2 を使った方がよいみたい

    - https://qiita.com/skyis/items/c85f2f60f4f73045e6bd

  - その上で下記2点がポイントになる（のでは、と思う）

    - incoming では Hubot から Let's Chat に通信するので Let's Chat 側の 8080 ポートを expose で公開する
    - outgoing では Let's Chat に通信するので Hubot 側の PORT 環境変数 の値を expose で公開する
    - コンテナ内外でポートを変えていないので ports はいらないかも...

- docker-compose up する

  - ```
    $ docker-compose up
    ```

- Let's Chat に bot でログインできることを確認する

  - Username
    - hoge
  - Password
    - 11111111

- http://localhost:8080 からログインすると bot もルームの中にいる

  - ![bot](https://github.com/ma1979/sutra/raw/master/20171126_Hubot-LetsChat/cap/%E3%83%86%E3%82%B9%E3%83%88%E7%94%A8%E3%83%AB%E3%83%BC%E3%83%A0%20%C2%B7%20Let's%20Chat%202017-11-26%2021-56-06.png)

- Ping 投げると pong を返してくれる

  - ![ping](https://github.com/ma1979/sutra/raw/master/20171126_Hubot-LetsChat/cap/%E3%83%86%E3%82%B9%E3%83%88%E7%94%A8%E3%83%AB%E3%83%BC%E3%83%A0%20%C2%B7%20Let's%20Chat%202017-11-26%2021-59-07.png)

- public な環境で使う場合は token や user&pass は再設定を。ß


---