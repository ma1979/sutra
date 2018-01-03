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
  RUN yum -y install gtk2
  RUN yum -y install libXtst
  RUN yum -y install libXScrnSaver
  RUN yum -y install GConf2
  RUN yum -y install alsa-lib

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
  ADD ./example.js /home/bot
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

    - こんなエラーが出る場合は、

      ```shell
        nightmare queuing process start +0ms
        nightmare queueing action "goto" for http://yahoo.com +8ms
        nightmare queueing action "type" +4ms
        nightmare queueing action "click" +0ms
        nightmare queueing action "wait" +1ms
        nightmare queueing action "evaluate" +0ms
        nightmare running +1ms
        electron:stderr /home/bot/node_modules/electron/dist/electron: error while loading shared libraries: libXtst.so.6: cannot open shared object file: No such file or directory +62ms
        nightmare electron child process exited with code 127: command not found - you may not have electron installed correctly +8ms
        nightmare electron child process not started yet, skipping kill. +2ms
      ```

    - 足りないファイルのパッケージを調べて yum install を dockerfile に足す

      ```
      $ yum provides libXtst.so.6
      Loaded plugins: fastestmirror, ovl
      ovl: Error while doing RPMdb copy-up:
      [Errno 13] Permission denied: '/var/lib/rpm/Obsoletename'
      Determining fastest mirrors
       * base: ftp.iij.ad.jp
       * epel: ftp.riken.jp
       * extras: ftp.iij.ad.jp
       * updates: ftp.iij.ad.jp
      libXtst-1.2.3-1.el7.i686 : X.Org X11 libXtst runtime library
      Repo        : base
      Matched from:
      Provides    : libXtst.so.6
      ```

      ```docker file
      RUN yum -y install libXtst
      ```

      - 参考
        - https://labs.oratta.net/2017/01/888/

    - デバッグ実行は xvfb-run 越しで動作するのが正解みたい

      ```shell
      $ xvfb-run node example.js
      ```

      - なにやらエラーが出るのでこの対応
        - http://abc.tnxv.me/post/50988759673/which-no-xauth-in-null-xvfb-run-error-xauth

    - というのも間違いで、先に Xvfb を起動させておくのが正解みたい

      ```shell
      #!/usr/bin/env bash
      set -e

      # Start Xvfb
      Xvfb :1 -screen 0 1024x768x24 > /dev/null &
      export DISPLAY=:1

      exec "$@"
      ```

      - これを Entrypoint にする

    - さらに、Xvfb を起動させるのに Dbus の ID とやらも必要

      ```shell
      $ dbus-uuidgen > /var/lib/dbus/machine-id
      ```


    - いったん hubot やら何やらをなくして、erectorn + nightmare で必要なものだけを残した dockerfile にすると、

      ```dockerfile
      FROM centos
      MAINTAINER ma1979

      ENV TZ=Asia/Tokyo

      # 日本語
      RUN rm -f /etc/rpm/macros.image-language-conf && \
          sed -i '/^override_install_langs=/d' /etc/yum.conf && \
          yum -y reinstall glibc-common && \
          yum clean all

      ENV LANG="ja_JP.UTF-8" \
          LANGUAGE="ja_JP:ja" \
          LC_ALL="ja_JP.UTF-8"

      RUN yum -y update && yum -y install epel-release

      RUN yum -y install nodejs
      RUN yum -y install npm
      RUN yum -y install git wget

      # nightmare electron
      RUN yum -y install xorg-x11-server-Xvfb xorg-x11-fonts*
      RUN yum -y groupinstall "Japanese Support"
      RUN yum -y install gtk2
      RUN yum -y install atk
      RUN yum -y install pango
      RUN yum -y install gdk-pixbuf2
      RUN yum -y install libXrandr
      RUN yum -y install GConf2
      RUN yum -y install libXcursor
      RUN yum -y install libXcomposite
      RUN yum -y install libnotify
      RUN yum -y install libXtst
      RUN yum -y install cups-libs
      RUN yum -y install glib2-devel
      RUN yum -y install libXScrnSaver
      RUN yum -y install alsa-lib
      RUN yum -y install dbus
      RUN yum clean all

      # Xvfb用
      RUN dbus-uuidgen > /var/lib/dbus/machine-id
      COPY entrypoint.sh /entrypoint
      RUN chmod +x /entrypoint

      # サンプル
      RUN mkdir /home/node
      ADD ./example.js /home/node

      WORKDIR /home/node
      RUN npm install electron nightmare

      ENTRYPOINT ["/entrypoint"]
      ```

    - example.js はこんな感じに変更

      ```javascript

      const Nightmare = require('nightmare');
      const nightmare = Nightmare({
          show: true,
          width: 1024, // 初期のブラウザ幅
          height: 768, // 初期のブラウザ高さ
          enableLargerThanScreen: true // ブラウザを画面より大きく出来るか
      });

      // Yahooにアクセス、ENDLABで検索
      const yahooSearch = () => {
          return new Promise((resolve, reject) => {
              nightmare
                  .goto('http://yahoo.co.jp')
                  .wait('#srchtxt')
                  .insert('#srchtxt', 'ENDLAB')
                  .click('#srchbtn')
                  .wait('#mIn')
                  .evaluate(() => {
                      var body = document.querySelector('body');

                      // 背景を白に
                      body.style.backgroundColor = '#fff';

                      // ページ全体の幅と高さを返す
                      return {
                          width: body.scrollWidth + (window.outerWidth - window.innerWidth),
                          height: body.scrollHeight + (window.outerHeight - window.innerHeight)
                      };
                  })
                  .then((result) => {
                      resolve(result);
                  });
          });
      }

      // 現在のページをスクリーンショット保存
      const screenShot = (r) => {
          return new Promise((resolve, reject) => {
              nightmare
                  .viewport(r.width, r.height)
                  .wait(1000)
                  .screenshot('./screenshot.png')
                  .then(() => {
                      resolve(nightmare.end());
                  });
          });
      }

      // 実行開始
      yahooSearch()
          .then((r) => {
              screenShot(r);
          });
      ```

    - これで docker build してコンテナ上で example.js を実行してみると

      ```shell
      $ node example.js
      $ ls
      example.js  node_modules  screenshot.png
      ```

      - screenshot.png ができた。文字化けしてるけどまあ成功

        ![cap](./img/screenshot.png)

    - この時点でひとまず docker push

      ```shell
      $ docker push ma1979/nightmare-hubot
      ```

      ​
