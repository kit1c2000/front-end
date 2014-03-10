<?php 
include("createsvg.php");
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>SVG</title>
<style>
		body { font: 0.7em sans-serif; margin: 0px; }

		.arc path { stroke: #fff; }
		.axis path,
		.axis line { fill: none; stroke: #000; shape-rendering: crispEdges; }
		.browser text { text-anchor: end; }
	</style>
</head>

<body>
<!--
<svg width="960" height="450">
  <polygon points="100,10 40,180 190,60 10,60 160,180"
  style="fill:lime;stroke:purple;stroke-width:5;fill-rule:evenodd;" />
  
Sorry, your browser does not support inline SVG.
</svg>
-->
<script src="http://d3js.org/d3.v3.min.js" charset="utf-8"></script>
<script>

//var m1 = 3;
//var y1 = 10

//var m2 = 2
//var y2 = 12;

//var totalm = (y2 - y1)*12 + (m2 - m1);

//alert(totalm);




var margin = { top: 12, right: 10, bottom: 22, left: 60 },
width = 960 - margin.left - margin.right,
height = 450 - margin.top - margin.bottom;

var parseDate = d3.time.format("%Y%m").parse;

var alternatingColorScale = function () {
var domain, range;

function scale(x) { return range[domain.indexOf(x)%20]; }
scale.domain = function(x) {
if(!arguments.length) return domain; domain = x; return scale; }
scale.range = function(x) {
if(!arguments.length) return range; range = x; return scale; }
return scale; }
var color = alternatingColorScale().range(["#efc050", "#d0417e", "#00947e", "#0c1e3c", "#766a62", "#dc241f", "#7fcdcd" , "#FF9900", "#99FF00", "#990033", "#996600", "#CC00CC", "#9999CC", "#33FFCC", "#006600", "#0000FF", "#660066", "#CC3300", "#993300", "#FF66FF"]);

d3.tsv("monthlyfunding.tsv", function(error, data) {
color.domain(d3.keys(data[0]).filter(function(key) { return key !== "date"; }));

data.forEach(function(d) {
d.date = parseDate(d.date); });

var x = d3.time.scale().range([0, width]);
var y = d3.scale.linear().range([height, 0]).domain([0, <?php echo $_SESSION['MonthlyFunding']; ?>]);

var xAxis = d3.svg.axis().scale(x).orient("bottom");
var yAxis = d3.svg.axis().scale(y).orient("left");

var area = d3.svg.area().x(function(d) { return x(d.date); })
.y0(function(d) { return y(d.y0); })
.y1(function(d) { return y(d.y0 + d.y); });

var stack = d3.layout.stack().values(function(d) { return d.values; });

var svg = d3.select("body").append("svg")
.attr("width", width + margin.left + margin.right)
.attr("height", height + margin.top + margin.bottom)
.append("g")
.attr("transform", "translate(" + margin.left + "," + margin.top + ")");

var browsers = stack(color.domain().map(function(name) {
return { name: name, values: data.map(function(d) {
return { date: d.date, y: d[name] / 1}; }) };
}));

x.domain(d3.extent(data, function(d) { return d.date; }));

var browser = svg.selectAll(".browser").data(browsers)
.enter().append("g").attr("class", "browser");

browser.append("path").attr("class", "area")
.attr("d", function(d) { return area(d.values); })
.style("fill", function(d) { return color(d.name); });

svg.append("g").attr("class", "x axis")
.attr("transform", "translate(0," + height + ")").call(xAxis);

svg.append("g").attr("class", "y axis").call(yAxis)
.append("text")
.attr("transform", "rotate(-90)")
.attr("y", 6)
.attr("dy", ".71em")
.style("text-anchor", "end")
.text("Monthly Funding (£)");

});



</script>
</body>

</html> 