/*jshint esversion: 6*/
class ProductPicklist {

	constructor(root) {
		this.root = root;
		this.name = this.root.getAttribute('data-name');
		this.title = this.root.getAttribute('data-title');
		this.source = this.root.getAttribute('data-source');
		this.searchInput = this.root.querySelector('input[type=text]');
		this.quantityInput = this.root.querySelector('input[type=number]');
		this.addButton = this.root.querySelector('a[title="Add"]');
		this.cancelButton = this.root.querySelector('a[title="Cancel"]');
		this.loader = this.root.querySelector('.loader');
		this.showSelectList = this.root.querySelector('.show-select-list');
		this.showSelectedList = this.root.querySelector('.show-selected-list');
		this.list = [];
		this.selectedItem = null;
		this.timeout = null;
		this.request = null;
		this.active = false;

		if (!this.source || !this.source.trim()) {
			console.error('[Product PickList Component] A valid source needs to be informed');
			return;
		}

		const fg = this.root.querySelector('.form-group');

		this.showSelectList.style.top = fg.offsetHeight + 5;
		this.showSelectList.style.width = fg.offsetWidth - 30;
		this.loader.style.top = fg.offsetHeight - 35;
		this.loader.style.left = fg.offsetWidth - 40;

		this._binds();
		this._loadPreset();
		this._addEventListeners();
	}

	_binds() {
		this._onBlur = this._onBlur.bind(this);
		this._onFocus = this._onFocus.bind(this);
		this._onKeyUp = this._onKeyUp.bind(this);
		this._onAdd = this._onAdd.bind(this);
		this._onCancel = this._onCancel.bind(this);
		this._fetchData = this._fetchData.bind(this);
		this._clear = this._clear.bind(this);
		this._onError = this._onError.bind(this);
		this._onReady = this._onReady.bind(this);
		this._selectValue = this._selectValue.bind(this);
		this._removeItem = this._removeItem.bind(this);
	}

	_addEventListeners() {
		this.searchInput.addEventListener('blur', this._onBlur, false);
		this.searchInput.addEventListener('focus', this._onFocus, false);
		this.searchInput.addEventListener('keyup', this._onKeyUp, false);
		this.addButton.addEventListener('click', this._onAdd, false);
		this.cancelButton.addEventListener('click', this._onCancel, false);
	}

	_onBlur() {
		if (this.searchInput.value.trim() === '') {
			this.showSelectList.innerHTML = '';
		}

		if (this.showSelectList.innerHTML === '') {
			this.showSelectList.style.display = 'none';
		}
	}

	_onFocus() {
		if (this.searchInput.value.trim() === '') {
			this._onKeyUp();
		}
	}

	_onKeyUp(evt) {
		if (evt && !this._isValidKey(evt.which || evt.keyCode)) {
			return;
		}

		const value = this.searchInput.value;

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

	_onAdd(evt) {
		evt.preventDefault();

		if (!this.selectedItem) {
			return;
		}

		if (this.quantityInput.value.trim()) {
			this.selectedItem.quantity = parseInt(this.quantityInput.value);
		}

		this.list.push(this.selectedItem);

		this.selectedItem = null;
		this.searchInput.focus();
		this.searchInput.value = '';
		this.quantityInput.value = '';

		this._updateShowSelectedList();
	}

	_onCancel(evt) {
		evt.preventDefault();

		this.selectedItem = null;
		this.searchInput.value = '';
		this.quantityInput.value = '';
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
		this.request.send("search=" + this.searchInput.value);
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
				return item.id == _item.id;
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
		let index = 1;

		list.forEach(item => {
			items += `
			<li>
				<a
					href="#"
					data-id="${item.id}"
					data-name="${item.name}"
					data-quantity="${item.quantity}"
					data-price="${item.price}"
					>
					${(
						item.picture &&
						`<img
							src="${item.picture}"
							title="${item.name}"
							alt="${item.name}">`
					)}
					${item.name} :
					$ ${item.price.toFixed(2)}
				</a>
			</li>
			`;
		});

		this.quantityInput.setAttribute('tabindex', -1);
		this.addButton.setAttribute('tabindex', -1);
		this.cancelButton.setAttribute('tabindex', -1);
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

		const itemId = this._getMaxListId();
		const id = evt.target.getAttribute('data-id');
		const name = evt.target.getAttribute('data-name');
		const quantity = evt.target.getAttribute('data-quantity');
		const price = evt.target.getAttribute('data-price');

		this.selectedItem = {
			itemId,
			id,
			name,
			quantity: parseInt(quantity),
			price: parseFloat(price)
		};

		this.searchInput.value = name;
		this.quantityInput.max = quantity;
		this.quantityInput.value = quantity;
		this.quantityInput.focus();
		this.showSelectList.innerHTML = '';
		this.showSelectList.style.display = 'none';
		this.quantityInput.removeAttribute('tabindex');
		this.addButton.removeAttribute('tabindex');
		this.cancelButton.removeAttribute('tabindex');
	}

	_getMaxListId() {
		let max = 0;

		this.list.forEach(item => {
			if (max < item.itemId) {
				max = item.itemId;
			}
		});

		return max + 1;
	}

	_updateShowSelectedList() {
		if (!this.list.length) {
			this.showSelectedList.innerHTML = '';
			this.showSelectedList.style.display = 'none';

			return;
		}

		const head = `<thead class="thead-inverse">
			<tr>
				<th style="width: 5%;">#</th>
				<th>${this.title}</th>
				<th style="width: 10%; text-align: right;">Quantity</th>
				<th style="width: 10%; text-align: right;">Price</th>
				<th style="width: 10%; text-align: right;">Subtotal</th>
				<th style="width: 5%;">Action</th>
			</tr>
		</thead>`;

		const items = [];
		let total = 0;

		this.list.forEach(item => {
			const subtotal = item.price * item.quantity;

			items.push(`<tr>
				<td class="id" style="text-align: right;">
					<input type="hidden" name="${this.name}[${item.itemId}][item-id]" value="${item.itemId}">
					<input type="hidden" name="${this.name}[${item.itemId}][id]" value="${item.id}">
					<input type="hidden" name="${this.name}[${item.itemId}][name]" value="${item.name}">
					<input type="hidden" name="${this.name}[${item.itemId}][quantity]" value="${item.quantity}">
					<input type="hidden" name="${this.name}[${item.itemId}][price]" value="${item.price}">
					${item.id}
				</td>
				<td class="name">${item.name}</td>
				<td class="quantity" style="text-align: right;">${item.quantity}</td>
				<td class="price" style="text-align: right;">$ ${item.price.toFixed(2)}</td>
				<td style="text-align: right;">$ ${subtotal.toFixed(2)}</td>
				<td>
					<a href="#" class="btn btn-sm btn-danger" data-item-id="${item.itemId}">
						<spam class="material-icons">delete</spam>
					</a>
				</td>
			</tr>`);

			total += subtotal;
		});

		const body = `<tbody>${items.join('')}</tbody>`;

		const foot = `<tfoot class="text-white bg-dark">
			<tr class="font-weight-bold text-right">
				<td colspan="4">Total</td>
				<td colspan="2">$ ${total.toFixed(2)}</td>
			</tr>
		</tfoot>`;

		const table = `<table class="table table-bordered table-striped table-responsive table-hover">${head}${body}${foot}</table>`;

		this.showSelectedList.innerHTML = table;
		this.showSelectedList.style.display = 'block';
		this._addRemoveListeners();
	}

	_loadPreset() {
		const rows = Array.from(this.showSelectedList.querySelectorAll('tbody tr'));

		this.list = rows.map(item => {
			const itemId = parseInt(item.getAttribute('data-item-id'));
			const id = parseInt(item.getAttribute('data-id'));
			const name = item.getAttribute('data-name');
			const quantity = parseInt(item.getAttribute('data-quantity'));
			const price = item.getAttribute('data-price');

			return {
				itemId,
				id,
				name,
				quantity,
				price: parseFloat(price.replace(/[^0-9.]/g, ''))
			};
		});

		console.log(this.list);

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
		const itemId = btn.getAttribute('data-item-id');

		this.list = this.list.filter(item => item.itemId != itemId);
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
			.querySelectorAll('.component__product-picklist')
			.forEach(component => new this(component));
	}

}

ProductPicklist.loadComponents();
