# trello の期限付きカードの通知を x 時間、y 分前に hubot から slack に通知させる coffee script を作成し、hubot - slack の docker image を作成する

- 写経元

  - https://qiita.com/ryotahirano/items/2d0999dd0f6b83b8cae1
  - https://qiita.com/hiconyan/items/94941517b4df774bda4b
    - https://github.com/hico-horiuchi/kaonashi/

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

- coffee スクリプトを書く

  ```coffeescript
  # Description:
  #  Trello Task Remind

  cronJob = require('cron').CronJob
  Trello = require('node-trello')
  moment = require('moment')

  createMsg = (data) ->
    msg = ""
    if data.member?
      msg += "@" + data.member + " "
    msg += "タスク警察や！" + "\n"
    msg += "「" + data.name + "」が期限：" + data.due + " を超えとるで！\n"
    msg += "\n"
    return msg

  module.exports = (robot) ->
    ORG = process.env.HUBOT_TRELLO_ORGANIZATION
    MEMBERS = []
    trello = new Trello(
      process.env.HUBOT_TRELLO_KEY,
      process.env.HUBOT_TRELLO_TOKEN
    )

    getOrganizationsMembers = ->
      url = "/1/organizations/#{ORG}/members"
      trello.get url, (err, data) =>
        if err
          return
        MEMBERS = data

    getMemberNameByID = (id) ->
      for member in MEMBERS
        if member.id is id
          return member.username
      return 'channel'

    getOrganizationsMembers()

    trelloListID = process.env.HUBOT_TRELLO_JOB_LIST_ID

    cronJob = new cronJob(process.env.HUBOT_TRELLO_CRON, () ->
      now = moment()
      envelope = room: HUBOT_SLACK_CHANNEL
      mention = ""

      trello.get "/1/lists/#{trelloListID}/cards", {}, (err, data) ->
        if err
          robot.send(err)
          return

        for card in data
          if !(card.due == null)
            due = moment(card.due)
            diff = now.diff(due, 'minutes')

            if diff >= 0
              remindData = {}
              remindData.name = card.name
              remindData.due = due.format("YYYY/MM/DD h:mm A")
              remindData.member = getMemberNameByID(card.idMembers[0])
              msg = createMsg(remindData)

              robot.send(envelope, mention + msg)
    )
    cronJob.start()
  ```

   - 環境変数
      - HUBOT_TRELLO_KEY : trello の API key
     - HUBOT_TRELLO_TOKEN : trello の token
     - HUBOT_TRELLO_ORGANIZATION : trello の team の id
     - HUBOT_SLACK_CHANNEL : 通知先の slack の チャンネル
     - HUBOT_TRELLO_JOB_LIST_ID : Todo:あとで board に変更
     - HUBOT_TRELLO_CRON : 通知の間隔 秒からの cron 書式
  - ポイント
    - node-trello の各メソッドは非同期コールバックなので逐次的には書けない
    - card の 担当者リスト（idMembers） には id しか入っていないので名前を引くには member API から username を引く必要がある

 -  dockerfile を修正する

    ```dockerfile
    FROM node
    MAINTAINER ma1979

    RUN npm install -g yo generator-hubot
    RUN npm list -g yo generator-hubot
    RUN useradd bot
    RUN mkdir /home/bot && chown bot.bot /home/bot

    USER bot
    WORKDIR /home/bot
    RUN  yo hubot --owner "ma1979" --name "bot" --description "Hubot image" --adapter slack
    # trello
    RUN npm install node-trello
    RUN npm install cron
    ADD scripts/trello.coffee scripts

    CMD cd /home/bot; bin/hubot --adapter slack
    ```

    - dockerfile で スクリプトを ADD する

    - 定期実行で cron も使うので npm install cron もする

 -  docker run で環境変数を渡す形で docker run する

    ```shell
    $ docker run --rm -e "HUBOT_SLACK_TOKEN=<slaktoken>" -e "HUBOT_TRELLO_KEY=<apikey>" -e "HUBOT_TRELLO_TOKEN=<trellotoken>" -e "HUBOT_TRELLO_ORGANIZATION=<teamid>" -e "HUBOT_TRELLO_JOB_LIST_ID=<listid>" -e "HUBOT_TRELLO_CRON=*/30 * * * * *" --name trello -d ma1979/trello-hubot
    ```

- docker pull する

