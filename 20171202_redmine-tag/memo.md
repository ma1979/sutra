# redmine

## redmine に redmine_tagging plugin をインストールする

- dockerfile を書く

  - redmine_tagging 分を足す

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

    # redmine_tagging のインストール
    RUN git clone https://github.com/Restream/redmine_tagging.git plugins/redmine_tagging
    RUN echo "bundle install --without test development" >> /docker-entrypoint.sh
    RUN echo "bundle exec rake acts_as_taggable_on_engine:install:migrations RAILS_ENV=production" >> /docker-entrypoint.sh
    RUN echo "bundle exec rake db:migrate RAILS_ENV=production" >> /docker-entrypoint.sh
    RUN echo "bundle exec rake redmine:plugins:migrate RAILS_ENV=production" >> /docker-entrypoint.sh

    # docker-entrypoint.sh で passenger start が plugin のインストール完了後に実行されるようにする
    RUN sed -i -e 's/exec "$@"//g' /docker-entrypoint.sh
    RUN echo 'exec "$@"' >> /docker-entrypoint.sh
    ```

    - activerecord でバージョンの競合

      ```shell
      redmine_1     | Fetching gem metadata from https://rubygems.org/.........
      redmine_1     | Fetching version metadata from https://rubygems.org/...
      redmine_1     | Fetching dependency metadata from https://rubygems.org/..
      redmine_1     | Resolving dependencies....
      redmine_1     | Bundler could not find compatible versions for gem "activerecord":
      redmine_1     |   In snapshot (Gemfile.lock):
      redmine_1     |     activerecord (= 3.2.22.2)
      redmine_1     |
      redmine_1     |   In Gemfile:
      redmine_1     |     acts-as-taggable-on (~> 4.0) was resolved to 4.0.0, which depends on
      redmine_1     |       activerecord (>= 4.0)
      redmine_1     |
      redmine_1     |     rails (= 3.2.22.2) was resolved to 3.2.22.2, which depends on
      redmine_1     |       activerecord (= 3.2.22.2)
      redmine_1     |
      redmine_1     | Running `bundle update` will rebuild your snapshot from scratch, using only
      redmine_1     | the gems in your Gemfile, which may resolve the conflict.
      redmine_redmine_1 exited with code 6
      ```

      - 公式を見ると 2.6 系は Rails4 に対応していないようなので無理なのかな。
        - http://www.redmine.org/projects/redmine/wiki/RedmineInstall/253
          - redmine の 2.6 脱却が先ということで。
