[TOC]

---

# Jupyter NoteBook環境構築〜HelloWorld

- 写経元
  - https://qiita.com/kshigeru/items/2cd504e927869163b4c8

---

# 環境構築編

- docker pull

  - ```shell
    $ docker pull jupyter/datascience-notebook
    ```

- 8888 ポートで jupyter を起動

  - ```shell
    $ docker run -d --name notebook -p 8888:8888 jupyter/datascience-notebook
    ```

  - 動いた

    - ![Jupyter Notebook 🔊 2017-11-25 16-09-57](https://github.com/ma1979/sutra/raw/master/20171125_JupyterNotebook/cap/Jupyter%20Notebook%20%F0%9F%94%8A%202017-11-25%2016-09-57.png)

    - コンテナに入って、

      - ```shell
        $ docker exec -it notebook bash
        ```

    - コマンドを実行して token を確認する

      - ```shell
        $ jupyter notebook list
        Currently running servers:
        http://localhost:8888/?token=cfc996979890b0cf3cd80b4a34ff3f20a92754849065e8a3 :: /home/jovyan
        ```

---

# Hello World編

## 新しい ipynb を作成する

- token を入力して Login する

  - ![Home 🔊 2017-11-25 16-15-40](https://github.com/ma1979/sutra/raw/master/20171125_JupyterNotebook/cap/Home%20%F0%9F%94%8A%202017-11-25%2016-15-40.png)
  - New で Python3 を選択する

    - ![Home 🔊 2017-11-25 16-15-40](https://github.com/ma1979/sutra/raw/master/20171125_JupyterNotebook/cap/Home%20%F0%9F%94%8A%202017-11-25%2016-16-56.png)

## sin/cosカーブを書く

- Jupyter NotebookのGUIと操作方法とは...

  - ln []ごとにコードブロックと実行結果がある
    - ![Untitled 🔊 2017-11-25 16-30-21](https://github.com/ma1979/sutra/raw/master/20171125_JupyterNotebook/cap/Untitled%20%F0%9F%94%8A%202017-11-25%2016-30-21.png)
    - コードブロックにコードを書いて Shift + Enter を押すと実行される

- モジュールを import する
  - ```python
    import math

    import bokeh.plotting as bplt
    import numpy as np
    ```

- bplt のおまじない
  - ```python
    bplt.output_notebook()
    ```

    - Bokehとは

      - http://shirabeta.net/Python-Visualization-Bokeh-basic-plotting.html#.Whkd-rbAPOQ

        - > [Bokeh](http://bokeh.pydata.org/)は**インタラクティブなグラフ**を作成できる**データ可視化ライブラリ**です。Pythonで作られています。
          >
          > 同じくPython製の可視化ライブラリである[matplotlib](http://matplotlib.org/)や[seaborn](http://seaborn.pydata.org/)と比較して、下記のような特徴があります。
          >
          > - グラフを画像ファイルとして生成するのではなく、WEBブラウザで表示できるHTMLファイルやWebアプリとして生成する
          > - JavaScriptを使った**インタラクティブなグラフ**を生成できる
          > - グラフをドラッグしてパンしたり、要素や範囲を選択できる
          > - ボタンやスライダー等を組み合わせることもできる
          > - Python以外にも、ScalaやR、Julia等からも利用可能
          > - **Jupyter Notebookでの表示にも対応**している
          >
          > ちなみに、Bokehとは写真用語である「ボケ」のことを指す英単語です。

      - 今回は bokeh.plotting を使った例

        - ![Python製の可視化ライブラリBokehの基本的な使い方 - シラベタ 2017-11-25 16-44-37](https://github.com/ma1979/sutra/raw/master/20171125_JupyterNotebook/cap/Python%E8%A3%BD%E3%81%AE%E5%8F%AF%E8%A6%96%E5%8C%96%E3%83%A9%E3%82%A4%E3%83%96%E3%83%A9%E3%83%AABokeh%E3%81%AE%E5%9F%BA%E6%9C%AC%E7%9A%84%E3%81%AA%E4%BD%BF%E3%81%84%E6%96%B9%20-%20%E3%82%B7%E3%83%A9%E3%83%99%E3%82%BF%202017-11-25%2016-44-37.png)

      - bplt.output_notebook()

        - notebook上で使うときのおまじない。一度宣言するとあとは show でグラフが表示される

- sin と cos の関数を定義する
  - ```python
    cycle = math.pi * 2
    x = np.linspace(-1 * cycle, cycle, 100)
    y1 = np.sin(x)
    y2 = np.cos(x)
    ```

    - numpy.linspace

      - -π から +π までを 100等間隔 にした場合の数列を生成している

        - https://deepage.net/features/numpy-linspace.html

        - > NumPyの`np.linspace`は、線形に等間隔な数列を生成する関数です。同様の数列を`np.arange`で生成することもできますが、`np.linspace`を使用したほうがコード量を減らすことができ、読みやすくスマートになります

- グラフにプロットする
  - ```python
    p = bplt.figure(title='sin/cos curve', plot_width=640, plot_height=320)

    p.circle(x, y1, color='red')
    p.triangle(x, y2, color='blue')
    bplt.show(p)
    ```

    - bplt.figure
      - plot するための figure オブジェクトの生成。
        - https://www.sambaiz.net/article/129/
      - p.circle
        - plot したポイントを○で描く
      - p.triangle
        - plot したポイントを△で描く
    - bplt.show
      - グラフを描画する

- 描画できた

  - ​

- pandas のモジュールを使って Yahoo! の API から日経平均を取得する

  - pandas を import する

    - ```python
      import datetime

      import bokeh.plotting as bplt
      import pandas.io.data as web
      ```

      - エラーが起きる

        - ```python
          ---------------------------------------------------------------------------
          ImportError                               Traceback (most recent call last)
          <ipython-input-15-afd494153e79> in <module>()
                2 
                3 import bokeh.plotting as bplt
          ----> 4 import pandas.io.data as web

          /opt/conda/lib/python3.6/site-packages/pandas/io/data.py in <module>()
                1 raise ImportError(
          ----> 2     "The pandas.io.data module is moved to a separate package "
                3     "(pandas-datareader). After installing the pandas-datareader package "
                4     "(https://github.com/pandas-dev/pandas-datareader), you can change "
                5     "the import ``from pandas.io import data, wb`` to "

          ImportError: The pandas.io.data module is moved to a separate package (pandas-datareader). After installing the pandas-datareader package (https://github.com/pandas-dev/pandas-datareader), you can change the import ``from pandas.io import data, wb`` to ``from pandas_datareader import data, wb``.
          ```

        - pandas は別途インストールが必要になったみたい

          - コンテナに入って pandas_datareader をインストールしておく

            - ```shell
              $ docker exec -it notebook bash
              ```

            - ```shell
              $ conda install -c https://conda.anaconda.org/anaconda pandas-datareader
              ```

      - ```python
        import datetime

        import bokeh.plotting as bplt
        import pandas_datareader.data as web
        ```

        - これで import できる

  - ```python
    bplt.output_notebook()
    ```

  - ```python
    start = datetime.date(2014, 1, 1)
    end = datetime.date.today()
    df = web.DataReader('^N225', 'yahoo', start, end)
    df.describe
    ```

    - これだとうまくいかないみたいなので以下で。

      - ```python
        start = datetime.date(2014, 1, 1)
        end = datetime.date.today()
        df = web.DataReader('NIKKEI225', 'fred', start, end)
        df.describe
        ```

        - https://qiita.com/akichikn/items/782033e746c7ee6832f5

  - ```python
    bplt.figure(title='日経平均', x_axis_type='datetime', plot_width=640, plot_height=320)
    p.segment(df.index, df.High, df.index, df.Low, color='black')

    bplt.show(p)
    ```

    - エラー

      - ```python
        ---------------------------------------------------------------------------
        AttributeError                            Traceback (most recent call last)
        <ipython-input-22-3c8b1e63d991> in <module>()
              1 bplt.figure(title='日経平均', x_axis_type='datetime', plot_width=640, plot_height=320)
        ----> 2 p.segment(df.index, df.High, df.index, df.Low, color='black')
              3 
              4 bplt.show(p)

        /opt/conda/lib/python3.6/site-packages/pandas/core/generic.py in __getattr__(self, name)
           2742             if name in self._info_axis:
           2743                 return self[name]
        -> 2744             return object.__getattribute__(self, name)
           2745 
           2746     def __setattr__(self, name, value):

        AttributeError: 'DataFrame' object has no attribute 'High'
        ```

        - ​