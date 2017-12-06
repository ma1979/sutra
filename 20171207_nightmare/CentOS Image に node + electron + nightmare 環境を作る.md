# CentOS Image に node + electron + nightmare + hubot 環境を作る

- 写経元

  - http://d.hatena.ne.jp/chocopurin/20160620/1466425969
  - https://qiita.com/dozo/items/de393588d5c267794ced
  - https://qiita.com/piyo7/items/d771559c0fb1386f1ffa
  - http://okakacacao.wpblog.jp/technology/nightmare-js-implementation

- dockerfile

  ```dockerfile
  FROM centos
  MAINTAINER ma1979

  ENV TZ=Asia/Tokyo

  RUN yum -y update && yum -y install epel-release && yum clean all

  RUN yum install -y nodejs npm

  RUN yum -y install xorg-x11-server-Xvfb "xorg-x11-fonts*"
  RUN yum -y groupinstall "Japanese Support"

  RUN npm install coffee-script
  RUN npm install -g yo generator-hubot
  RUN npm list -g yo generator-hubot
  RUN useradd bot
  RUN ls -al /home
  #RUN mkdir /home/bot && chown bot.bot /home/bot

  USER bot
  WORKDIR /home/bot
  RUN  yo hubot --owner "ma1979" --name "bot" --description "Hubot image" --adapter lets-chat

  # lets-chat
  RUN npm install cron
  RUN npm install electron nightmare
  ADD scripts/login.coffee scripts

  RUN echo "#!/bin/bash" > /home/bot/run_hubot.sh
  RUN echo "bin/hubot --adapter lets-chat" >> /home/bot/run_hubot.sh

  ADD ./example.js ~/
  RUN chmod 777 /home/bot/run_hubot.sh

  CMD cd /home/bot; sh run_hubot.sh
  ```

- electron + nightmare のサンプルを動かす

  - サンプル

    ```javascript
    var Nightmare = require('nightmare');
    var nightmare = Nightmare({ show: true });

    nightmare
      .goto('http://yahoo.com')
      .type('form[action*="/search"] [name=p]', 'github nightmare')
      .click('form[action*="/search"] [type=submit]')
      .wait('#main')
      .evaluate(function () {
        return document.querySelector('#main .searchCenterMiddle li a').href
      })
      .end()
      .then(function (result) {
        console.log(result)
      })
      .catch(function (error) {
        console.error('Search failed:', error);
      });
    ```

  - デバッグ実行

    ```shell
    $ DEBUG=* node example.js
    ```

    ​