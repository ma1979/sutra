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
  - ​

  ​