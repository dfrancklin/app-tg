class PicklistComponent {

	constructor(root) {
		this.root = root;
		this.source = this.root.getAttribute('data-source');
		this.input = this.root.querySelector('input[type=text]');
		this.timeout = null;
		this.request = null;
		this.active = false;

		if (!this.source || !this.source.trim()) {
			console.error('[PickList Component] A valid source needs to be informed');
			return;
		}

		this._binds();
		this._addEventListeners();
	}

	_binds() {
		this._onKeyUp = this._onKeyUp.bind(this);
		this._fetchData = this._fetchData.bind(this);
		this._clear = this._clear.bind(this);
		this._onError = this._onError.bind(this);
		this._onReady = this._onReady.bind(this);
	}

	_addEventListeners() {
		this.input.addEventListener('keyup', this._onKeyUp, false);
	}

	_onKeyUp(evt) {
		if (!this._isValidKey(evt.which || evt.keyCode)) {
			return;
		}

		const value = this.input.value;

		if (value.trim() === '') {
			return;
		}

		if (this.timeout) {
			console.log('clearing...');
			clearTimeout(this.timeout);
			this.timeout = null;
		}

		if (this.active) {
			console.log('aborting...');
			this.request.abort();
		}

		this.timeout = setTimeout(this._fetchData, 500);
	}

	_fetchData() {
		console.log('search', this.input.value, 'on source', this.source);

		this.active = true;

		this.request = new XMLHttpRequest();
		this.request.onabort = this._clear;
		this.request.onerror = this._onError;
		this.request.onreadystatechange = this._onReady;
		this.request.open("POST", this.source, true);
		this.request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		this.request.send("search=" + this.input.value);
	}

	_clear() {
		this.active = false;
		this.request = null;
		this.timeout = null;
	}

	_onError() {
		this._clear();
		console.error(error);
	}

	_onReady() {
		if (this.request.readyState === 4 && this.request.status === 200) {
			console.log(this.request.response);
			this._clear();
		}
	}

	_isValidKey(key) {
		if (
			key === 8 ||
			key === 32 ||
			key === 46 ||
			key === 194 ||
			(
				key >= 48 &&
				key <= 90
			) ||
			(
				key >= 96 &&
				key <= 107
			) ||
			(
				key >= 109 &&
				key <= 111
			) ||
			(
				key >= 186 &&
				key <= 191
			) ||
			(
				key >= 219 &&
				key <= 222
			)
		) {
			return true;
		}

		return false;
	}

	static loadComponents() {
		document
			.querySelectorAll('.component__picklist')
			.forEach(component => new this(component));
	}

}

PicklistComponent.loadComponents();
