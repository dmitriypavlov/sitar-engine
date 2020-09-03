document.addEventListener("DOMContentLoaded", () => init());

function init() {

	load("/component/jquery-3.5.1.min.js", () => {

		load("/template/template.js");
		
		if (typeof editable != "undefined") load("/editable.js");
		console.log(("%c%s"), "color: red", "runtime.js: ready");
	});
}

function load(url, callback) {

	var type = url.split(".").pop();
	var res = undefined;

	if (type == "js") {

		res = document.createElement("script");
		res.setAttribute("src", url);
		if (typeof callback == "function") res.onload = callback;

	} else if (type == "css") {

		res = document.createElement("link");
		res.setAttribute("rel", "stylesheet");
		res.setAttribute("href", url);
	}

	if (typeof res != "undefined") document.getElementsByTagName("head")[0].appendChild(res);
}