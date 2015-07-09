  

  /* 汎用メソッド */


/* 一定の範囲内の乱数取得 */
function rand(min, max) {
  switch(arguments.length){
  case 1:
    return Math.random() * max;
  case 2:
    return Math.random() * (max - min) + min;
  }
}


/* 一定の範囲内に収める */
function limit(value, minValue, maxValue) {
  if (value < minValue) {
    return minValue;
  }
  if (maxValue < value) {
    return maxValue;
  }
  return value;
};
