# redmine

## redmine の docker Image を作って最小構成の redmine を docker-compose で立ち上げる

- dockerfile を書く

  - bundle install が通るように json 1.8.3 などを入れておく

    ```dockerfile
    FROM redmine:2.6.10-passenger
    MAINTAINER ma1979

    RUN apt-get update

    # redmine_persist_wfmt の native ext 用
    RUN apt-get install -y gcc make pkg-config imagemagick libmagickcore-dev libmagickwand-dev libsqlite3-dev
    RUN gem install json -v 1.8.3
    RUN gem install rmagick -v 2.13.4
    RUN gem install sqlite3 -v 1.3.11
    ```

    - json を入れるために gcc make を入れておく

    - rmagick を入れるために ImageMagick 諸々を入れておく

      - https://qiita.com/yuiseki/items/55bded2e32d07aef23ab

    - sqlite3 を入れるために libsqlite3-dev を入れておく

- docker build する

  ```shell
  $ docker build -t ma1979/redmine .
  ```

- docker push する

  ```shell
  $ docker push ma1979/redmine
  ```

- docker-compose.yml を書く

  ```dockerfile
  version: '2'
  services:
    mysql:
      image: mysql:latest
      volumes_from:
        - data-mysql
      ports: 
        - "3306:3306"
      environment:
        MYSQL_ROOT_PASSWORD: secret
        MYSQL_DATABASE: redmine

    data-mysql:
      image: busybox:latest
      volumes: 
        - /var/lib/mysql

    redmine:
      image: ma1979/redmine
      ports:
        - "81:3000"
      environment:
        REDMINE_RELATIVE_URL_ROOT: /redmine
        DB_USER: redmine
        DB_PASS: password
        SMTP_USER: address@hoge.com
        SMTP_PASS: password
  ```

- 起動する

  ```shell
  $ docker-compose up
  ```

- admin でログインする

  - https://hub.docker.com/r/_/redmine/
    - admin / admin





# テキストフォーマットを textile と markdown で選べるようにする

## redmine_persist_wfmt をインストールする

- plugin はこれ

  - https://github.com/pinzolo/redmine_persist_wfmt

- redmine コンテナに入る

  ```shell
  $ docker exec -it redmine_redmine_1 bash
  ```

    - git clone して bundle install する

      ```shell
      $ cd ./plugin
      $ git clone https://github.com/pinzolo/redmine_persist_wfmt.git plugins/redmine_persist_wfmt
      $ bundle install --path=vendor/bundle --binstubs=bundle_bin --without test development
      ```

      - — path と — binstubs を設定する必要はないのかも

    - migrate する

        ```shell
        $ bundle exec rake redmine:plugins:migrate NAME=redmine_persist_wfmt RAILS_ENV=production
        ```

    - persist_all する

        ```shell
        $ bundle exec rake pwfmt:persist_all FORMAT=textile RAILS_ENV=production
        ```

    - 再起動する

        ```shell
        $ touch tmp/restart.txt
        ```



- 入力エリアに textile か markdown かのツールボックスが出る

  ![toolbox](https://github.com/ma1979/sutra/raw/master/20171201_redmine-textile-markdown/redmine/cap/%E3%83%90%E3%82%B0%20%231:%20hoge%20-%20hoge%20-%20Redmine%202017-12-02%2001-58-47.png)

- dockerfile に反映させる

  ```dockerfile
  FROM redmine:2.6.10-passenger
  MAINTAINER ma1979

  RUN apt-get update

  # redmine_persist_wfmt の native ext 用
  RUN apt-get install -y gcc make pkg-config imagemagick libmagickcore-dev libmagickwand-dev libsqlite3-dev
  RUN gem install json -v 1.8.3
  RUN gem install rmagick -v 2.13.4
  RUN gem install sqlite3 -v 1.3.11

  # redmine_persist_wfmt のインストール
  RUN git clone https://github.com/pinzolo/redmine_persist_wfmt.git plugins/redmine_persist_wfmt
  RUN echo "bundle install --without test development" >> /docker-entrypoint.sh
  RUN echo "bundle exec rake redmine:plugins:migrate NAME=redmine_persist_wfmt RAILS_ENV=production" >> /docker-entrypoint.sh
  RUN echo "bundle exec rake pwfmt:persist_all FORMAT=textile RAILS_ENV=production" >> /docker-entrypoint.sh
  RUN sed -i -e 's/exec "$@"//g' /docker-entrypoint.sh
  RUN echo 'exec "$@"' >> /docker-entrypoint.sh
  ```

  - 試しに redmine の Image だけ立ち上げてみるときはこれ

    ```shell
    $ docker run -p 81:3000 -e DB_USER="redmine" -e DB_PASS="password" ma1979/redmine passenger start
    ```

  - passenger start する前に plugin のインストールをするには？については、ベースのイメージに入っている /docker-entrypoint.sh の引数を実行する前に入れ込む方法しか分からなかった

    - この部分

      ```dockerfile
      RUN sed -i -e 's/exec "$@"//g' /docker-entrypoint.sh
      RUN echo 'exec "$@"' >> /docker-entrypoint.sh
      ```

- docker push する

  ```shell
  $ docker push ma1979/redmine
  ```

  ​

  ​
