  

  /* 子メソッド */


/* FPS計算 */
function calculateFps(now) {
  var fps = Math.ceil(1000 / (now - lastAnimationFrameTime));
  lastAnimationFrameTime = now;

  if (now - lastFpsUpdateTime > 1000) {
    lastFpsUpdateTime = now;
  }

  return fps;
}


/* html要素列挙 */
function enumElements(func) {
  var ps, left, top, right, bottom;
  returnValue = false;
  $('.container').find(enumElementType).not(enumElementNotType).each(function(){
    if (!$(this).is(':visible')) {
      return;
    }
    if (undefined === $(this).attr('class')) {
      switch($(this).get(0).tagName){
      case 'DIV': case 'IFRAME':
        return;
      }
    }
    if ('' === $(this).text()) {
      switch($(this).get(0).tagName){
      case 'A':
        return;
      }
    }
    ps = $(this).offset();
    ps.left -= $(window).scrollLeft();
    ps.top -= $(window).scrollTop();
    if (768 <= canvas.nowWidth) {
      ps.top -= 50;
    }
    left = Math.floor(ps.left / tip.width);
    top = Math.floor(ps.top / tip.height);
    right = Math.ceil(($(this).innerWidth()) / tip.width);
    bottom = Math.ceil(($(this).innerHeight()) / tip.height);
    right = limit(right+left+1, 0, canvas.tipWidth);
    bottom = limit(bottom+top+1, 0, canvas.tipHeight);
    left = limit(left, 0, canvas.tipWidth);
    top = limit(top, 0, canvas.tipHeight);

    returnValue = func(left, top, right, bottom, $(this));
    if (returnValue) {
      return false;
    }
  });
  return returnValue;
}

/* 要素の背景を変える */
function elementBackGroundMapData(isForce) {
  
  if (!isForce) {
    var toUpdate = enumElements(function(left, top, right, bottom, obj){
      if (!elements.isRegistered(left, top, right, bottom)) {
        return obj.text();
      }
      return false;
    });
    if (false == toUpdate) {
      return;
    }
  }
  
  elements.clear();
  mapdata.fillWithFlag(0, 0, canvas.tipWidth, canvas.tipHeight, 30, 8, MAPF_FRONT, function(m){
    return isBackMap(m.flag);
  });

  if (true == isDebug['element']) {
    $('#rpg_info').html('<br/><br/><br/><br/><br/>');
  }
  enumElements(function(left, top, right, bottom, obj){
    if (true == isDebug['element']) {
      $('#rpg_info').append(''+ elements.register(left, top, right, bottom) +' : '+ obj.get(0).tagName +'['+ obj.text() +']<br/>');
    } else {
      elements.register(left, top, right, bottom);
    }
    mapdata.fillTile(left, top, right, bottom, 36, 4, MAPF_BACK);
  });

  mapdata.forceReDraw();
}
