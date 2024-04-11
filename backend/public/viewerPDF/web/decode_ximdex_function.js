/**
 * Function obfuscated in final file of viewer.js
 */
await (async function() {
	const queryparams = new URLSearchParams(window.location.search);
	const hash = window.location.hash.substring(1);
	console.log('## LOAD XIMDEX PDF ##')
	const qp = new URLSearchParams(atob(hash))
	if (qp.has('dx') && qp.get('dx') == 1) {
		window.dx = true
		const $download_button = document.querySelector('#download')
		$download_button.style.display = 'inherit'

	}
	if (!window.ximdex) window.ximdex = {}
	const i = await fetch(atob(hash))
	const bl = await i.blob()
	const url = URL.createObjectURL(bl)
	window.ximdex.url = url}
)();