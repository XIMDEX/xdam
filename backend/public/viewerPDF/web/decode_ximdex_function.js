/**
 * Function obfuscated in final file of viewer.js
 */

await (async function() {
	let statusLoadBar = 0;
	console.log('## LOADING XIMDEX PDF ##')
	const incrementLoadbar = (loadBar, increment = 1) => {
		if (statusLoadBar < 98) statusLoadBar += increment
		if (statusLoadBar > 100) statusLoadBar = 100;
		loadBar.style.setProperty("--progressBar-percent", statusLoadBar+"%");
		console.log(statusLoadBar)
	}
	const loadBar = document.querySelector('#loadingBar')
	// loadBar.classList.toggle('hidden');
	incrementLoadbar(loadBar)
	const hash = window.location.hash.substring(1);
	const qp = new URLSearchParams(atob(hash))
	if (qp.has('dx') && qp.get('dx') == 1) {
		window.dx = true
		const $download_button = document.querySelector('#download')
		$download_button.style.display = 'inherit'

	}
	if (!window.ximdex) window.ximdex = {}
	let loaded = false;
	let time_interval = setInterval(() =>{
		if (statusLoadBar < 95) {
			incrementLoadbar(loadBar)
		}
	}, 500)
	const i = await fetch(atob(hash))
	const bl = await i.blob()
	const url = URL.createObjectURL(bl)

	clearInterval(time_interval)
	incrementLoadbar(loadBar, 100)
	loadBar.classList.toggle('hidden')
	
	window.ximdex.url = url
})();