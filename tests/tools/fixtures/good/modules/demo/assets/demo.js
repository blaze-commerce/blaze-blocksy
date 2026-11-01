( function () {
	const el = document.querySelector( '.bc-demo__label' );
	if ( el ) {
		el.textContent = el.dataset.label || '';
		el.innerHTML = '<span></span>';
	}
} )();
