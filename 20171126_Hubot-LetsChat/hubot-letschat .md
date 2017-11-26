[TOC]

---

# Hubot と Let's Chat を連携させる

- 写経元
  - https://qiita.com/na1234/items/7098d638e88ff922dd2e

---

## Let's Chat の bot 用のアカウントを作った状態のDocker Image を作る

- **<u>Docker Image は既に出来ているので使うだけの場合はこの手順は飛ばしてOK</u>**

- docker-compose.ymd を書く

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

- テスト用のルームを作る

  - ![Rooms · Let's Chat 2017-11-26 11-57-58](https://github.com/ma1979/sutra/raw/master/20171126_Hubot-LetsChat/cap/Rooms%20%C2%B7%20Let's%20Chat%202017-11-26%2011-57-58.png)
  - こんな感じで
    - ![Rooms · Let's Chat 2017-11-26 11-59-55](https://github.com/ma1979/sutra/raw/master/20171126_Hubot-LetsChat/cap/Rooms%20%C2%B7%20Let's%20Chat%202017-11-26%2011-59-55.png)

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
      mongodump --archive=/tmp/letschat-hubot-mongo-initial.gz --gzip --db letschat
      2017-11-26T05:00:48.507+0000    writing letschat.users to archive '/tmp/letschat-hubot-mongo-initial.gz'
      2017-11-26T05:00:48.510+0000    writing letschat.rooms to archive '/tmp/letschat-hubot-mongo-initial.gz'
      2017-11-26T05:00:48.515+0000    writing letschat.sessions to archive '/tmp/letschat-hubot-mongo-initial.gz'
      2017-11-26T05:00:48.520+0000    writing letschat.messages to archive '/tmp/letschat-hubot-mongo-initial.gz'
      2017-11-26T05:00:48.538+0000    done dumping letschat.sessions (1 document)
      2017-11-26T05:00:48.540+0000    writing letschat.usermessages to archive '/tmp/letschat-hubot-mongo-initial.gz'
      2017-11-26T05:00:48.557+0000    done dumping letschat.rooms (1 document)
      2017-11-26T05:00:48.602+0000    done dumping letschat.messages (0 documents)
      2017-11-26T05:00:48.609+0000    done dumping letschat.users (1 document)
      2017-11-26T05:00:48.616+0000    done dumping letschat.usermessages (0 documents)
      ```

  - これを GitHub に push する

    - コンテナからdumpファイルを取り出す

      - ```shell
        $ docker cp d44336c25626:/tmp/letschat-hubot-mongo-initial.gz .
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
        $ docker build -t [DockerHub UserID]/letschat-hubot-mongo .
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

## bot 用アカウントが入った Let's Chat を立ち上げる

- push した Image を使って docker-compose するように docker-compose.yml を書き直す

  - ```yaml
    app:
      image: sdelements/lets-chat
      links:
        - mongo
      ports:
        - 8080:8080
        - 5222:5222

    mongo:
      image: ma1979/letschat-hubot-mongo
    ```

- docker-compose up する

  - ```
    $ docker-compose up
    ```

  - ​


- - ​
