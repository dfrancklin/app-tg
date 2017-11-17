class PicklistComponent {

	constructor(root) {
		this.root = root;
		this.name = this.root.getAttribute('data-name');
		this.title = this.root.getAttribute('data-title');
		this.label = this.root.getAttribute('data-label');
		this.value = this.root.getAttribute('data-value');
		this.source = this.root.getAttribute('data-source');
		this.input = this.root.querySelector('input[type=text]');
		this.loader = this.root.querySelector('.loader');
		this.showSelectList = this.root.querySelector('.show-select-list');
		this.showSelectedList = this.root.querySelector('.show-selected-list');
		this.list = [];
		this.timeout = null;
		this.request = null;
		this.active = false;

		if (!this.source || !this.source.trim()) {
			console.error('[PickList Component] A valid source needs to be informed');
			return;
		}

		if (!this.label || !this.label.trim()) {
			console.error('[PickList Component] A valid label needs to be informed');
			return;
		}

		if (!this.value || !this.value.trim()) {
			console.error('[PickList Component] A valid value needs to be informed');
			return;
		}

		const fg = this.root.querySelector('.form-group');

		this.showSelectList.style.top = fg.offsetHeight + 5;
		this.loader.style.top = fg.offsetHeight - 35;

		this._binds();
		this._loadPreset();
		this._addEventListeners();
	}

	_binds() {
		this._onBlur = this._onBlur.bind(this);
		this._onFocus = this._onFocus.bind(this);
		this._onKeyUp = this._onKeyUp.bind(this);
		this._fetchData = this._fetchData.bind(this);
		this._clear = this._clear.bind(this);
		this._onError = this._onError.bind(this);
		this._onReady = this._onReady.bind(this);
		this._selectValue = this._selectValue.bind(this);
		this._removeItem = this._removeItem.bind(this);
	}

	_addEventListeners() {
		this.input.addEventListener('blur', this._onBlur, false);
		this.input.addEventListener('focus', this._onFocus, false);
		this.input.addEventListener('keyup', this._onKeyUp, false);
	}

	_onBlur() {
		if (this.showSelectList.innerHTML === '') {
			this.showSelectList.style.display = 'none';
		}
	}

	_onFocus() {
		if (this.input.value.trim()) {
			this._onKeyUp();
		}
	}

	_onKeyUp(evt) {
		if (evt && !this._isValidKey(evt.which || evt.keyCode)) {
			return;
		}

		const value = this.input.value;

		if (value.trim() === '') {
			return;
		}

		if (this.timeout) {
			clearTimeout(this.timeout);
			this.timeout = null;
		}

		if (this.active) {
			this.request.abort();
		}

		this.showSelectList.style.display = 'block';
		this.timeout = setTimeout(this._fetchData, 500);
	}

	_fetchData() {
		this.active = true;
		this.loader.style.display = 'block';

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
		this.loader.style.display = 'none';
	}

	_onError() {
		this._clear();
		console.error(error);
	}

	_onReady() {
		if (this.request.readyState === 4 && this.request.status === 200) {
			if (!this.request.response || this.request.response.trim()) {
				this._updateShowSelectList([]);
			}

			let list = JSON.parse(this.request.response);
			list = this._removeUsedItems(list);
			this._updateShowSelectList(list);
			this._clear();
		}
	}

	_removeUsedItems(list) {
		const newList = list.filter(item => {
			return !this.list.some(_item => {
				return item[this.value] == _item.value;
			});
		});

		return newList;
	}

	_updateShowSelectList(list) {
		if (!list.length) {
			this.showSelectList.innerHTML = '';
			return;
		}

		let items = '';

		list.forEach(item => {
			items += `<li><a href="#" data-value="${item[this.value]}">${item[this.label]}</a></li>`;
		});

		this.showSelectList.innerHTML = `<ul>${items}</ul>`;
		this._addSelectListeners();
	}

	_addSelectListeners() {
		const links = this.showSelectList.querySelectorAll('a');

		links.forEach(item => {
			item.addEventListener('click', this._selectValue);
		});
	}

	_selectValue(evt) {
		evt.preventDefault();
		const value = evt.target.getAttribute('data-value');
		const label = evt.target.innerText;

		this.list.push({
			value,
			label
		});
		this.showSelectList.innerHTML = '';
		this.showSelectList.style.display = 'none';
		this.input.value = '';
		this._updateShowSelectedList();
	}

	_updateShowSelectedList() {
		if (!this.list.length) {
			this.showSelectedList.innerHTML = '';
			this.showSelectedList.style.display = 'none';

			return;
		}

		let items = `<thead class="thead-inverse">
			<tr>
				<th style="width: 5%;">#</th>
				<th>${this.title}</th>
				<th style="width: 5%;">Action</th>
			</tr>
		</thead>`;

		items += '<tbody>';

		this.list.forEach(item => {
			items += `
				<tr>
					<td class="value" style="text-align: right;">
						<input type="hidden" name="${this.name}[]" value="${item.value}">
						${item.value}
					</td>
					<td class="label">${item.label}</td>
					<td>
						<a href="#" class="btn btn-sm btn-danger" data-value="${item.value}">
							<spam class="material-icons">delete</spam>
						</a>
					</td>
				</tr>`;
		});

		items += `</tbody>`;

		this.showSelectedList.innerHTML = `<table class="table table-bordered table-striped table-responsive table-hover">${items}</table>`;
		this.showSelectedList.style.display = 'block';
		this._addRemoveListeners();
	}

	_loadPreset() {
		const rows = Array.from(this.showSelectedList.querySelectorAll('tbody tr'));

		this.list = rows.map(item => {
			const value = item.querySelector('.value').innerText;
			const label = item.querySelector('.label').innerText;

			return {
				value,
				label
			};
		});

		this._addRemoveListeners();
	}

	_addRemoveListeners() {
		const links = this.showSelectedList.querySelectorAll('a');

		links.forEach(item => {
			item.addEventListener('click', this._removeItem, false);
		});
	}

	_removeItem(evt) {
		evt.preventDefault();
		const btn = evt.currentTarget;
		const value = btn.getAttribute('data-value');

		this.list = this.list.filter(item => item.value != value);
		this._updateShowSelectedList();
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
