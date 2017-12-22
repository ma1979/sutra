[TOC]

---

# Hubot 環境構築

- 写経元

  - https://qiita.com/miyamiya/items/91d1bac25764a24e820e

- Dockerfile を作る

  - 最初から Dockerfile を書くならやらなくてよい

    - Docker から image を pull する

      - node.js 公式のイメージを取得する

        - ```shell
          $ docker pull node
          ```

    - Docker run してコンテナ内に入る

      - ```shell
        $ docker run --rm -it -v /etc/localtime:/etc/localtime:ro --name "bot-base" node /bin/sh
        ```

  - Dockerfile を書く

    - ```dockerfile
      FROM node
      MAINTAINER [DockerHub UserID]

      RUN npm install -g yo generator-hubot
      RUN npm list -g yo generator-hubot
      RUN useradd bot
      RUN mkdir /home/bot && chown bot.bot /home/bot

      USER bot
      WORKDIR /home/bot
      RUN  yo hubot --owner "ma1979" --name "bot" --description "Hubot image" --adapter slack

      CMD cd /home/bot; bin/hubot --adapter slack
      ```

      - MAINTAINER は DockerHub に push するときの自分のID名
      - RUN と CMD の違い
        - RUN
          - ビルド時にコンテナ内で実行される
        - CMD
          - 完成したイメージからコンテナを作成するときに実行される
        - 参考
          - https://qiita.com/YusukeHigaki/items/044164837daa5e845d50

  - build する

    - ```shell
      $ docker build -t [DockerHub UserID]/bot-base .
      ```
        - イメージ名には後の push のため DockerHub の UserID をつける
    - ```Shell
      Sending build context to Docker daemon  2.048kB
      Step 1/10 : FROM node
       ---> c1d02ac1d9b4
      Step 2/10 : MAINTAINER ma1979
       ---> Running in 14058dd4b12e
       ---> fc7cc120dd42
      Removing intermediate container 14058dd4b12e
      Step 3/10 : RUN npm install -g yo generator-hubot
       ---> Running in 60b29dc20c2c
      npm WARN deprecated CSSselect@0.4.1: the module is now available as 'css-select'
      npm WARN deprecated CSSwhat@0.4.7: the module is now available as 'css-what'
      npm WARN deprecated minimatch@2.0.10: Please update to minimatch 3.0.2 or higher to avoid a RegExp DoS issue
      npm WARN deprecated minimatch@0.3.0: Please update to minimatch 3.0.2 or higher to avoid a RegExp DoS issue
      /usr/local/bin/yo -> /usr/local/lib/node_modules/yo/lib/cli.js
      /usr/local/bin/yo-complete -> /usr/local/lib/node_modules/yo/lib/completion/index.js

      > spawn-sync@1.0.15 postinstall /usr/local/lib/node_modules/yo/node_modules/spawn-sync
      > node postinstall

        > yo@2.0.0 postinstall /usr/local/lib/node_modules/yo
        > yodoctor
        
        Yeoman Doctor
        Running sanity checks on your system

        ✔ Global configuration file is valid
        ✔ NODE_PATH matches the npm root
        ✔ Node.js version
        ✔ No .bowerrc file in home directory
        ✔ No .yo-rc.json file in home directory
        ✔ npm version

        Everything looks all right!
        + yo@2.0.0
        + generator-hubot@0.4.0
        added 693 packages in 67.531s
         ---> d4f49261a9fb
        Removing intermediate container 60b29dc20c2c
        Step 4/10 : RUN npm list -g yo generator-hubot
         ---> Running in 574c7a968e94
        /usr/local/lib
        +-- generator-hubot@0.4.0
        `-- yo@2.0.0

         ---> 2806ea3adf09
        Removing intermediate container 574c7a968e94
        Step 5/10 : RUN useradd bot
         ---> Running in c30d12f9954e
         ---> f174ec79be91
        Removing intermediate container c30d12f9954e
        Step 6/10 : RUN mkdir /home/bot && chown bot.bot /home/bot
         ---> Running in 54ef95ddf4fa
         ---> 40ecc828e055
        Removing intermediate container 54ef95ddf4fa
        Step 7/10 : USER bot
         ---> Running in 97e8821dec6d
         ---> 8d14ff18c0bd
        Removing intermediate container 97e8821dec6d
        Step 8/10 : WORKDIR /home/bot
         ---> 0d1e49fc6945
        Removing intermediate container b1bbd3523b42
        Step 9/10 : RUN yo hubot --owner "ma1979" --name "bot" --description "Hubot image" --adapter slack
         ---> Running in 1599acb776f7
                             _____________________________
                            /                             \
           //\              |      Extracting input for    |
          ////\    _____    |   self-replication process   |
         //////\  /_____\   \                             /
         ======= |[^_/\_]|   /----------------------------
          |   | _|___@@__|__
          +===+/  ///     \_\
           | |_\ /// HUBOT/\\
           |___/\//      /  \\
                 \      /   +---+
                  \____/    |   |
                   | //|    +===+
                    \//      |xx|

           create bin/hubot
           create bin/hubot.cmd
           create Procfile
           create README.md
           create external-scripts.json
           create hubot-scripts.json
           create .gitignore
           create package.json
           create scripts/example.coffee
           create .editorconfig
                             _____________________________
         _____              /                             \
         \    \             |   Self-replication process   |
         |    |    _____    |          complete...         |
         |__\\|   /_____\   \     Good luck with that.    /
           |//+  |[^_/\_]|   /----------------------------
          |   | _|___@@__|__
          +===+/  ///     \_\
           | |_\ /// HUBOT/\\
           |___/\//      /  \\
                 \      /   +---+
                  \____/    |   |
                   | //|    +===+
                    \//      |xx|

        npm WARN deprecated connect@2.30.2: connect 2.x series is deprecated
        npm WARN deprecated node-uuid@1.4.8: Use uuid module instead
        npm notice created a lockfile as package-lock.json. You should commit this file.
        npm WARN hubot-help@0.2.2 requires a peer of coffee-script@^1.12.6 but none is installed. You must install peer dependencies yourself.

        + hubot-scripts@2.17.2
        + hubot-google-translate@0.2.1
        + hubot-help@0.2.2
        + hubot-diagnostics@0.0.2
        + hubot-maps@0.0.3
        + hubot-google-images@0.2.7
        + hubot-redis-brain@0.0.4
        + hubot@2.19.0
        + hubot-heroku-keepalive@1.0.3
        + hubot-pugme@0.1.1
        + hubot-slack@4.4.0
        + hubot-rules@0.1.2
        + hubot-shipit@0.2.1
        added 231 packages in 27.223s
         ---> 66737a68588a
        Removing intermediate container 1599acb776f7
        Step 10/10 : CMD cd /home/bot/hubot; bin/hubot --adapter slack
         ---> Running in 9fd942801dee
         ---> e879202a026e
        Removing intermediate container 9fd942801dee
        Successfully built e879202a026e
        Successfully tagged bot-base:latest

      ```

        - できた

  - build したイメージを使って起動する

    - ```shell
      $ docker run -itd -v /etc/localtime:/etc/localtime:ro -e "HUBOT_SLACK_TOKEN=slackから取得したtoken" --name "作成するコンテナ名" [DockerHub UserID]/bot-base
      ```

      - 起動時に /bin/sh や /bin/bash などを指定すると CMD で記述した処理が実行されないみたいなので注意
        - http://blog.queth.net/docker-cotainer-start-shell-exec.html

- DockerHub に Image を Push する

  - ```shell
    $ docker push ma1979/bot-base
    ```

    - ![push](https://raw.githubusercontent.com/ma1979/sutra/master/20171126_Hubot/cap/ma1979%20-%20Docker%20Hub%202017-11-26%2007-54-46.png)
    
    - image の名称を ma1979/slack-bot に変えた
