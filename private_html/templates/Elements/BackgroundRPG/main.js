  

  /* RPG main */


/* 常時変更対象マップ */
function rpgSetMapData(isForce) {
  // 背景の動的認識
  elementBackGroundMapData(isForce);

  // アニメーション
}

/* ウィンドウ関係 */
function rpgWindow()
{
  canvas.nowWidth = $('#rpg_window').width();
  canvas.nowHeight = $('#rpg_window').height();
  if (canvas.nowWidth <= canvas.width && canvas.nowHeight <= canvas.height) {
    return;
  }
  // 大きくなる場合だけサイズ変更
  var oldTipWidth = canvas.tipWidth
  var oldTipHeight = canvas.tipHeight
  if (canvas.width < canvas.nowWidth) {
    canvas.width = canvas.nowWidth;
  }
  if (canvas.height < canvas.nowHeight) {
    canvas.height = canvas.nowHeight;
  }
  canvas.tipWidth = Math.ceil(canvas.width/tip.width);
  canvas.tipHeight = Math.ceil(canvas.height/tip.height);
  if (768 <= canvas.nowWidth) {
    canvas.height -= 50;
  }
  $('#rpg_canvas_master').attr("width", canvas.width);
  $('#rpg_canvas_master').attr("height", canvas.height);
  $('#rpg_canvas_slave').attr("width", canvas.width);
  $('#rpg_canvas_slave').attr("height", canvas.height);

  // マップデータの初期値書き込み
  console.log(''+oldTipWidth+'=>'+canvas.tipWidth+' x '+oldTipHeight+'=>'+canvas.tipHeight);
  mapdata.fill(oldTipWidth, oldTipHeight, canvas.tipWidth, canvas.tipHeight, 30, 8);

  // マップデータの拡張
  if (0 != oldTipWidth) {
    mapdata.fill(oldTipWidth, 0, canvas.tipWidth, oldTipHeight, 30, 8);
  }
  if (0 != oldTipHeight) {
    mapdata.fill(0, oldTipHeight, oldTipWidth, canvas.tipHeight, 30, 8);
  }

  mapdata.chara.pos(canvas.tipWidth/2, canvas.tipHeight/2);

  rpgSetMapData(true);
  mapdata.forceReDraw();
}

/* リソース初期化 */
function rpgResource()
{
  canvases = {
    master: document.getElementById('rpg_canvas_master').getContext('2d'),
    slave: document.getElementById('rpg_canvas_slave').getContext('2d'),
  };

  mapdata.src($('#rpg_window').attr('data-src'));
  mapdata.charaSrc($('#rpg_window').attr('data-chara'));
}

/* 変数初期化 */
function rpgVariable()
{
  canvas = {width:0, height:0, tipWidth:0, tipHeight:0, nowWidth:0, nowHeight:0};
  nowCanvasId = 0;

  tip = {x:0, y:0, width:16, height:16};
  character = {x:0, y:0, width:24, height:32};
  mapdata = new Map();
  elements = new ElementRect();

  enumElementType = $('#rpg_window').attr('data-find');
  enumElementNotType = $('#rpg_window').attr('data-not');

  c = 1;
  isDebug = [];
  debugList = $('#rpg_window').attr('data-debug').split(',');
  for(var key in debugList) {
    isDebug[debugList[key]] = true;
  }
}

/* 初期化 */
function rpgInitalize()
{
  rpgVariable();
  rpgResource();
  rpgWindow();


  setInterval(rpgMain, 1000);
  mapdata.forceReDraw();
}

/* RPGメイン */
function rpgMain()
{
  rpgSetMapData();
}

/* 描画メイン */
function rpgDrawMain(now)
{
  if (undefined != isDebug['fps']) {
    $('#rpg_console').text('Allocated[' + canvas.width + ' x ' + canvas.height + '] FPS:' + calculateFps(now));
  }

  imgData = canvases.slave.getImageData(0, 0, canvas.width, canvas.height);
  try {
    rpgDraw(canvases.slave);
  } catch(e) {
    if ('NS_ERROR_NOT_AVAILABLE' == e.name) {

      rpgDraw(canvases.slave);
    } else {
      throw e;
    }
  }
  canvases.master.putImageData(imgData, 0, 0);
}

/* 描画 */
function rpgDraw(ctx)
{
  ctx.globalAlpha = 1.0; // 描画の透明度を50％にする

  ctx.fillStyle = "rgb(255, 255, 255)";
  ctx.fillRect(0, 0, canvas.width, canvas.height);

  // 背景
  mapdata.draw(ctx);

  /* グリッド線 */
  if (undefined != isDebug['gridline']) {
    ctx.beginPath();
    for(var x=0; x<canvas.tipWidth; x++){
      ctx.moveTo(x*tip.width, 0);
      ctx.lineTo(x*tip.width, canvas.height);
    }
    for(var y=0; y<canvas.tipHeight; y++){
      ctx.moveTo(0, y*tip.height);
      ctx.lineTo(canvas.width, y*tip.height);
    }
    ctx.stroke();
  }
  /**/

}

