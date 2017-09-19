var dpr = window.devicePixelRatio;
		document.write("<meta name=\"viewport\" content=\"width=device-width,initial-scale=" + (1 / dpr) + ",minimum-scale=" + (1 / dpr) + ",maximum-scale=" + (1 / dpr) + ",user-scalable=no\">")
		var html = document.querySelector("html");
		html.style.fontSize = html.offsetWidth / 7.5 + "px";
