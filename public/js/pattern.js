var colours = ["YlGn", "YlGnBu", "GnBu", "BuGn", "PuBuGn", "PuBu", "BuPu", "RdPu", "Purples", "Blues", "Greens", "RdYlBu", "Spectral", "RdYlGn"];
var pattern = Trianglify({
	width: window.innerWidth,
	height: window.innerHeight,
	cell_size: 60 + Math.random() * 100,
	x_colors: colours[Math.floor(Math.random()*colours.length)],
	y_colors: 'match_x',
	stroke_width: 2
});
document.body.appendChild(pattern.canvas())