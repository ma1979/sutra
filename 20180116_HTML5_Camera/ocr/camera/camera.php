<!DOCTYPE html>
<html lang="ja" dir="ltr" itemscope itemtype="http://schema.org/Article">
<head>
  <meta charset="utf-8">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
</head>
<body>
    <div class="camera">
        <video id="video">Video stream not available.</video>
    </div><br>
    <button id="startbutton">Take photo</button><br>
    <canvas id="canvas">
    <textarea id="readStr"></textarea>
    </canvas>

<script>
// 参考
// https://qiita.com/niisan-tokyo/items/91e94cce014796f731d3

let width = 320    // We will scale the photo width to this
let height = 0     // This will be computed based on the input stream

let streaming = false

let video = null
let canvas = null
let photo = null
let startbutton = null
let constrains = { video: true, audio: false }

/**
 * ユーザーのデバイスによるカメラ表示を開始し、
 * 各ボタンの挙動を設定する
 *
 */
function startup() {
  video = document.getElementById('video')
  canvas = document.getElementById('canvas')
  photo = document.getElementById('photo')
  startbutton = document.getElementById('startbutton')

  videoStart()

  video.addEventListener('canplay', function(ev){
    if (!streaming) {
      height = video.videoHeight / (video.videoWidth/width)

      video.setAttribute('width', width)
      video.setAttribute('height', height)
      canvas.setAttribute('width', width)
      canvas.setAttribute('height', height)
      streaming = true
    }
  }, false)

  // 「take photo」ボタンをとる挙動を定義
  startbutton.addEventListener('click', function(ev){
    takepicture()
    ev.preventDefault()
  }, false);

  clearphoto()
}

/**
 * カメラ操作を開始する
 */
function videoStart() {
  streaming = false
  navigator.mediaDevices.getUserMedia(constrains)
  .then(function(stream) {
      video.srcObject = stream
      video.play()
  })
  .catch(function(err) {
      console.log("An error occured! " + err)
  })
}


/**
 * canvasの写真領域を初期化する
 */
function clearphoto() {
  let context = canvas.getContext('2d')
  context.fillStyle = "#AAA"
  context.fillRect(0, 0, canvas.width, canvas.height)
}

/**
 * カメラに表示されている現在の状況を撮影する
 */
function takepicture() {
  let context = canvas.getContext('2d')
  if (width && height) {
    canvas.width = width
    canvas.height = height
    context.drawImage(video, 0, 0, width, height)
    send()
  } else {
    clearphoto()
  }
}

function send() {
    data = canvas.toDataURL('image/png').replace(/^.*,/, '')
    $.ajax('read.php', {
        data : {image: data},
        method: 'POST'
    }).then(res => {
            alert(res)
        },
        error => {
            alert("error")
        })
}

startup()

</script>
</body>