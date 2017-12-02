# Bing Speech API

- 無料で API キーを発行する

  - https://azure.microsoft.com/ja-jp/try/cognitive-services/?api=speech-api

    ![api](https://github.com/ma1979/sutra/raw/master/20171202_BingSpeechAPI/cap/Cognitive%20Services%20%E8%A9%A6%E7%94%A8%E3%82%A8%E3%82%AF%E3%82%B9%E3%83%9A%E3%83%AA%E3%82%A8%E3%83%B3%E3%82%B9%20%7C%20Microsoft%20Azure%202017-12-02%2016-11-53.png)

- クイックスタートガイド へ

  - powershell のサンプルコードでひとまず投げてみる

    ```powershell
    $SpeechServiceURI =
    'https://speech.platform.bing.com/speech/recognition/interactive/cognitiveservices/v1?language=en-us&format=detailed'

    # $OAuthToken is the authorization token returned by the token service.
    $RecoRequestHeader = @{
      'Ocp-Apim-Subscription-Key' = 'YOUR_SUBSCRIPTION_KEY';
      'Transfer-Encoding' = 'chunked'
      'Content-type' = 'audio/wav; codec=audio/pcm; samplerate=16000'
    }

    # Read audio into byte array
    $audioBytes = [System.IO.File]::ReadAllBytes("YOUR_AUDIO_FILE")

    $RecoResponse = Invoke-RestMethod -Method POST -Uri $SpeechServiceURI -Headers $RecoRequestHeader -Body $audioBytes

    # Show the result
    $RecoResponse
    ```

  - 修正すると、

    ```

    ```

  - 音声ファイルは AWS Polly で作成

  - 途中。。
