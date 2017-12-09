# hubot を IBM Cloud（旧bluemix） の無料枠（ライト・プラン）で動かす

- 写経元

  - https://qiita.com/Amebayashi/items/ca979ec6f925abc7713f

- bluemix のアカウントを作成する

- bluemix にログインする

- まずリソースを作成する

  ![](https://github.com/ma1979/sutra/raw/master/20171209_bluemix_hubot/img/%E3%82%BF%E3%82%99%E3%83%83%E3%82%B7%E3%83%A5%E3%83%9B%E3%82%99%E3%83%BC%E3%83%88%E3%82%99%20-%20IBM%20Cloud%20%F0%9F%94%8A%202017-12-09%2018-17-40.png)

- 無料枠（ライトプラン）で始めると、ライトプラン対象のサービスでフィルタされるみたい

  ![](https://github.com/ma1979/sutra/raw/master/20171209_bluemix_hubot/img/%E3%82%AB%E3%82%BF%E3%83%AD%E3%82%AF%E3%82%99%20-%20IBM%20Cloud%20%F0%9F%94%8A%202017-12-09%2018-18-32.png)

- Container Régistry を選ぶ

  ![](https://github.com/ma1979/sutra/raw/master/20171209_bluemix_hubot/img/%E3%82%AB%E3%82%BF%E3%83%AD%E3%82%AF%E3%82%99%20-%20IBM%20Cloud%20%F0%9F%94%8A%202017-12-09%2018-21-55.png)

  - Kubernetes は難しそうなので...

- 始めに を押す

  ![](https://github.com/ma1979/sutra/raw/master/20171209_bluemix_hubot/img/IBM%20Cloud%20%F0%9F%94%8A%202017-12-09%2018-24-44.png)

  - 日本語でよかった

- クイックスタートの通りにやればよさそう。なので、まずは IBM Cloud 用の CLI をインストールする

  ![](https://github.com/ma1979/sutra/raw/master/20171209_bluemix_hubot/img/IBM%20Cloud%20%F0%9F%94%8A%202017-12-09%2018-29-11.png)

  - Install the IBM Cloud CLI. のリンクに飛ぶ

  - 飛んだ先で OS を選んでインストーラをダウンロードする

    - インストールは指示に従うのみで OK

  - 同様に、Install the Docker CLI. のリンクに飛ぶ

    - これは Docker for XX がすでに入っていれば飛ばしてよい
      - 自分の環境には Docker for Mac がもう入っているので飛ばす

  - container registry の plugin をインストールする

    ```shell
    $ bx plugin install container-registry -r Bluemix
    リポジトリー 'Bluemix' から 'container-registry' を検索しています...
    プラグイン 'container-registry 0.1.253' がリポジトリー 'Bluemix' 内で見つかりました
    バイナリー・ファイルをダウンロードしようとしています...
     20.98 MiB / 20.98 MiB [=================================================================] 100.00% 14s
    21999584 バイトがダウンロードされました
    バイナリーをインストールしています...
    OK
    プラグイン 'container-registry 0.1.253' は /Users/chelseagirl/.bluemix/plugins/container-registry に正常にインストールされました。 'bx plugin show container-registry' を使用して詳細を表示してください。
    ```

  - IBM Cloud にログインする

    ```shell
    $ bx login -a https://api.au-syd.bluemix.net
    API エンドポイント: https://api.au-syd.bluemix.net

    Email> ***

    Password> 
    認証中です...
    OK

    アカウントを選択します (または Enter キーを押してスキップします):
    1. ***'s Account (***)
    数値を入力してください> 1
    ターゲットのアカウント ***'s Account (***)

    ターゲットのリソース・グループ Default

                             
    API エンドポイント:   https://api.au-syd.bluemix.net (API バージョン: 2.92.0)   
    地域:                 au-syd   
    ユーザー:             *** 
    アカウント:           ***'s Account (***)   
    リソース・グループ:   Default   
    組織:                    
    スペース:                

    ヒント: Cloud Foundry アプリケーションおよびサービスを管理している場合
    - 'bx target --cf' を使用して Cloud Foundry 組織/スペースを対話式にターゲットにするか、'bx target -o ORG -s SPACE' を使用して組織/スペースをターゲットにします。
    - 現行の Bluemix CLI コンテキストを使用して Cloud Foundry CLI を実行する場合は、'bx cf' を使用します。
    ```

- namespace を作る

  ```shell
  $ bx cr namespace-add ***
  名前空間「***」を追加しています...

  名前空間「***」は正常に追加されました

  OK
  ```

- docker と IBM Cloud を同期させる？のかな？

  ```shell
  $ bx cr login
  「registry.au-syd.bluemix.net」にログインしています...
  失敗
  「registry.au-syd.bluemix.net」に対する「docker login」が失敗しました。エラー: WARNING! Using --password via the CLI is insecure. Use --password-stdin.
  Error response from daemon: Get https://registry.au-syd.bluemix.net/v2/: unsupported: The requested authentication method is not supported. Run the `bx cr login` command. To use registry tokens, use `docker login -u token` and your registry token as the password.
  ```

  - docker login した状態でやらないといけなさそうなので docker login する

    ```
    $ docker login
    ```

    ​