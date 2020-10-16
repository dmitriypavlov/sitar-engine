function edit(node) {
	var editable = node.attr("contenteditable");
	var key = node.attr("id");
	var value = null;

	if (editable == "false") {
		if (typeof preedit === "function") preedit(node);
		value = node.html();
		value = value.replaceAll("<!--?", "<?");
		value = value.replaceAll("?-->", "?>");
		node.text(value);
		node.attr("contenteditable", "true");
		console.log("editable.js: " + key);
	} else {
		value = node.text();
		save(node, key, value);
		node.html(value);
		node.attr("contenteditable", "false");
		console.log("editable.js: " + value);
		if (typeof postedit === "function") postedit(node);
	}

	// move caret

	var range = document.createRange();
	range.selectNodeContents($(node).get(0));
	range.collapse(false);
	var selection = document.getSelection();
	selection.removeAllRanges();
	selection.addRange(range);
}

function save(node, key, value) {

	$.ajax({
		method: "POST",
		url: "/",
		data: { "key": key, "value": value },
		success: function(result) {

			if (result == "200 OK") {
				node.addClass("success");
				setTimeout(function() { node.removeClass("success") }, 1000);
			} else alert("Save error");

			console.log("editable.js: " + result);
		},
		error: function() { alert("Save error") }
	});
}

$("data").each(function() {

	// add title

	$(this).attr("title", $(this).attr("id"));

	// edit data

	$(this).on("click", function(e) {

		if (e.altKey) {

			edit($(this));
			return false;
		}

	});

	$(this).on("keydown", function(e) {

		function insert(text) {
			e.preventDefault();
			document.execCommand("insertText", false, text);
		}

		if (e.code == "Enter") {
			e.preventDefault();
			document.execCommand("insertHTML", false, "\n");
		}

	});

});

window.addEventListener("beforeunload", function(e) {

	// check contenteditable

	$("data[contenteditable=true]").first().each(function() {
		e.preventDefault();
		e.returnValue = null;
	});

});

console.log(("%c%s"), "color: red", "editable.js: ready");