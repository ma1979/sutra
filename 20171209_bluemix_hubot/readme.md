# hubot を IBM Cloud（旧bluemix） の無料枠（ライト・プラン）で動かす

- 写経元

  - https://qiita.com/Amebayashi/items/ca979ec6f925abc7713f

- bluemix のアカウントを作成する

- bluemix にログインする

- まずリソースを作成する

  画像

- 無料枠（ライトプラン）で始めると、ライトプラン対象のサービスでフィルタされるみたい

  画像

- Container Régistry を選ぶ

  画像

  - Kubernetes は難しそうなので...

- 始めに を押す

  画像

  - 日本語でよかった

- クイックスタートの通りにやればよさそう。なので、まずは IBM Cloud 用の CLI をインストールする

  - Install the IBM Cloud CLI. のリンクに飛ぶ
  - 飛んだ先で OS を選んでインストーラをダウンロードする
    - インストールは指示に従うのみで OK
  - 同様に、Install the Docker CLI. のリンクに飛ぶ
    - これは Docker for XX がすでに入っていれば飛ばしてよい
      - 自分の環境には Docker for Mac がもう入っているので飛ばす
  - ​

  ​