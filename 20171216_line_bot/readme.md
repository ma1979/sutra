# LINE Bot を動かす

- 写経元

  - https://qiita.com/n0bisuke/items/ceaa09ef8898bee8369d

## linebot 開発環境のイメージを作成する 

- node イメージを run する

  ```shell
  $ docker run -it --rm -v <コンテナに作ったソースを保存するホストのディレクトリの絶対パス>/linebot/mylinebot/src:/home/node/mylinebot/src --name linebot ma1979/linebot bash
  ```


- package.json を作成する

  ```shell
  $ cd /home/node
  $ mkdir mylinebot
  $ cd mylinebot
  $ npm init -y
  ```

- SDK を入れる

  ```shell
  $ npm i --save @line/bot-sdk express
  ```

- 以上を dockerfile に書く

  ```dockerfile
  FROM node
  MAINTAINER ma1979

  RUN apt-get update && apt-get -y install vim

  RUN mkdir -p /home/node/mylinebot/src

  WORKDIR /home/node/mylinebot
  RUN npm init -y
  RUN npm i --save @line/bot-sdk express
  ```

- docker build する

  ```shell
  $ docker build -t ma1979/linebot .
  ```

- docker push する

  ```shell
  $ docker push ma1979/linebot
  ```

## linebot 開発環境のイメージを使う

- docker run する

  ```shell
  $ docker run -it --rm -v <ホストPCのソースファイル置き場までの絶対パス>/mylinebot/src:/home/node/mylinebot/src --name linebot ma1979/linebot bash
  ```

  - /home/node/mylinebot までコンテナ
  - /home/node/mylinebot/src はホスト