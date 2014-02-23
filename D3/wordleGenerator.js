
  var fill = d3.scale.category20();
var width = $(window).width();
var margin = {
    top: 150,
    right: 100,
    bottom: 20,
    left: 120
};
  d3.layout.cloud().size([1200, 600])
      .words([
        " analytic visual natcor intensive offer "," drug pharmaceutical molecule compound lead "," traffic road network congestion flow "," datum health related lifestyle processing "," inspection nde defect array destructive "," layer structure made thin year "," sensor sensing monitoring detection technology "," utility pipe service leak leakage "," infrastructure national resilience service failure "," datum cloud computing service provider "," single force individual tip examine "," power electronics dc electrical converter "," memory switching device switch resistance "," water drinking management health regulatory "," fuel cell hydrogen technology power ","tracking batch end length trajectory "," environment built design physical capture "," step ab initio workflow performed "," relationship link extent causal predictive "," resolution imaging optical microscopy image "," carbon emission reduction dioxide reduce "].map(function(d) {
       // return {text: d, size: 1 + Math.random() * 50};
	   return {text: d, size: 17};
      }))
      .padding(1)
      .rotate(0)
      .font("Helvetica")
      .fontSize(function(d) { return d.size; })
      .on("end", draw)
      .start();

  function draw(words) {
    d3.select("body").append("svg")
        .attr("width", width)
        .attr("height", 600)
      .append("g")
        .attr("transform", "translate(650,175)")
      .selectAll("text")
        .data(words)
      .enter().append("text")
        .style("font-size", function(d) { return d.size + "px"; })
        .style("font-family", "Helvetica")
        .style("fill", function(d, i) { return fill(i); })
        .attr("text-anchor", "middle")
        .attr("transform", function(d) {
          return "translate(" + [d.x,d.y] + ")";
        })
        .text(function(d) { return d.text; });
  }
