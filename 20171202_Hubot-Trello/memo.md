# trello の期限付きカードの通知を x 時間、y 分前に hubot から slack に通知させる

- 写経元

  - https://qiita.com/ryotahirano/items/2d0999dd0f6b83b8cae1

- hubot の dockerfile を書く

  - [前に書いた slack - hubot の Image を使う](https://github.com/ma1979/sutra/blob/master/20171126_Hubot/Hubot%20%E7%92%B0%E5%A2%83%E6%A7%8B%E7%AF%89.md)

- trello の API key を発行する

  - trello にログインして、
    - https://trello.com/
  - 開発者向けにいくと画面に表示されている
    - https://trello.com/app-key

- token を発行する

  ```
  https://trello.com/1/authorize?key=<取得したAPIKey>&name=&expiration=never&response_type=token&scope=read,write
  ```

  - 結果はこういう感じ

    ```
    You have granted access to your Trello information.

    To complete the process, please give this token:

      <token文字列>
    ```

    ​

- board のリストを取得する

  ```
  https://trello.com/1/members/<Trelloでの自分のID>/boards?key=<取得したAPIKey>&token=<取得したToken>&fields=name
  ```
  - 結果はこういう感じ

    ```json
    [{"name":"ボード1","id":"id1"},{"name":"ボード2","id":"id2"}]
    ```

- List の一覧を取得する

  ```
  https://trello.com/1/boards/<取得したボードのID>/lists?key=<取得したAPIKey>&token=<取得したToken>&fields=name
  ```

  - 結果はこういう感じ

    ```json
    [{"id":"id1","name":"未定"},{"id":"id2","name":"完了"}]
    ```

- Card の一覧を取得する

  ```
  https://trello.com/1/lists/<取得したリストのID>/cards?key=<取得したAPIKey>&token=<取得したToken>
  ```

  - Card の一覧と属性が取れる

- まずは docker run してコンテナ内で作業する

  ```shell
  $ docker run -d --rm -e "HUBOT_SLACK_TOKEN=<token>" ma1979/bot-base
  ```


- hubot から trello の API を使うために node モジュールをインストールする

  ```shell
  $ cd /home/bot
  $ npm install node-trello --save
  ```

- ​