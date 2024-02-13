/*
Template Name: Admin Pro Admin
Author: Wrappixel
Email: niravjoshi87@gmail.com
File: js
*/
$(function () {
  "use strict";
  // ==============================================================
  // Newsletter
  // ==============================================================

  var chart2 = new Chartist.Bar(
    ".amp-pxl",
    {
      labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
      series: [
        [91, 51, 31, 71, 51, 101, 31, 31, 71, 51, 101, 31],
        [61, 31, 91, 51, 41, 61, 41, 91, 51, 41, 61, 41],
      ],
    },
    {
      axisX: {
        // On the x-axis start means top and end means bottom
        position: "end",
        showGrid: false,
      },
      axisY: {
        // On the y-axis start means left and end means right
        position: "start",
      },
      high: "121",
      low: "0",
      plugins: [Chartist.plugins.tooltip()],
    }
  );

  var chart = [chart2];
});


/*
Template Name: Admin Pro Admin
Author: Wrappixel
Email: niravjoshi87@gmail.com
File: js
*/
$(function () {
  "use strict";
  // ==============================================================
  // Newsletter
  // ==============================================================

  var chart2 = new Chartist.Bar(
    "#loss",
    {
      labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
      series: [
        [9, 5, 3, 7, 5, 10, 3, 3, 7, 5, 10, 3],
        [6, 3, 9, 5, 4, 6, 4, 3, 7, 5, 10, 3],
      ],
    },
    {
      axisX: {
        // On the x-axis start means top and end means bottom
        position: "end",
        showGrid: false,
      },
      axisY: {
        // On the y-axis start means left and end means right
        position: "start",
        labelInterpolationFnc: function(value) {
          return (value) + '%';
        }
      },
      high: "12",
      low: "0",
      plugins: [Chartist.plugins.tooltip()],
    }
  );

  var chart = [chart2];
});

$(function () {
  "use strict";
  // ==============================================================
  // Newsletter
  // ==============================================================

  var data = {
    series: [115198, 511529]
  };
  
  var sum = function(a, b) { return a + b };
  
  const pie = new Chartist.Pie('.ct-chart', data, {
    labelInterpolationFnc: function(value) {
      return ('(' + NumberWithCommas(value) + ') ') + (Math.round(value / data.series.reduce(sum) * 100)) + '%';
    }
  });
});

function NumberWithCommas(number) {
  return number.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");
}
