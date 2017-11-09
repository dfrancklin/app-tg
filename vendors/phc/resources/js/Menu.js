class Menu {

  constructor() {
    this.menuEl = document.querySelector('.js-menu');

    if (!this.menuEl) {
      return;
    }

    this.showButtonEl = document.querySelector('.js-menu-show');
    this.hideButtonEl = document.querySelector('.js-menu-hide');
    this.menuContainerEl = document.querySelector('.js-menu-container');
    // Control whether the container's children can be focused
    // Set initial state to inert since the drawer is offscreen
    this.detabinator = new Detabinator(this.menuContainerEl);
    this.detabinator.inert = true;

    this.showMenu = this.showMenu.bind(this);
    this.hideMenu = this.hideMenu.bind(this);
    this.blockClicks = this.blockClicks.bind(this);
    this.onTouchStart = this.onTouchStart.bind(this);
    this.onTouchMove = this.onTouchMove.bind(this);
    this.onTouchEnd = this.onTouchEnd.bind(this);
    this.onTransitionEnd = this.onTransitionEnd.bind(this);
    this.update = this.update.bind(this);

    this.startX = 0;
    this.currentX = 0;
    this.touchingMenu = false;

    this.transitionEndProperty = null;
    this.transitionEndTime = 0;

    this.supportsPassive = undefined;
    this.addEventListeners();
  }

  // apply passive event listening if it's supported
  applyPassive() {
    if (this.supportsPassive !== undefined) {
      return this.supportsPassive ? { passive: true } : false;
    }

    // feature detect
    let isSupported = false;

    try {
      document.addEventListener('test', null, {
        get passive() {
          isSupported = true;
        }
      });
    } catch (e) { }

    this.supportsPassive = isSupported;

    return this.applyPassive();
  }

  addEventListeners() {
    this.showButtonEl.addEventListener('click', this.showMenu);
    this.hideButtonEl.addEventListener('click', this.hideMenu);
    this.menuEl.addEventListener('click', this.hideMenu);
    this.menuContainerEl.addEventListener('click', this.blockClicks);

    this.menuEl.addEventListener('touchstart', this.onTouchStart, this.applyPassive());
    this.menuEl.addEventListener('touchmove', this.onTouchMove, this.applyPassive());
    this.menuEl.addEventListener('touchend', this.onTouchEnd);
  }

  onTouchStart(evt) {
    if (!this.menuEl.classList.contains('menu--visible'))
      return;

    this.startX = evt.touches[0].pageX;
    this.currentX = this.startX;

    this.touchingMenu = true;
    requestAnimationFrame(this.update);
  }

  onTouchMove(evt) {
    if (!this.touchingMenu)
      return;

    this.currentX = evt.touches[0].pageX;
  }

  onTouchEnd(evt) {
    if (!this.touchingMenu)
      return;

    this.touchingMenu = false;

    const translateX = Math.min(0, this.currentX - this.startX);
    this.menuContainerEl.style.transform = '';

    if (translateX < 0) {
      this.hideMenu();
    }
  }

  update() {
    if (!this.touchingMenu)
      return;

    requestAnimationFrame(this.update);

    const translateX = Math.min(0, this.currentX - this.startX);
    this.menuContainerEl.style.transform = `translateX(${translateX}px)`;
  }

  blockClicks(evt) {
    evt.stopPropagation();
  }

  onTransitionEnd(evt) {
    if (evt.propertyName != this.transitionEndProperty && evt.elapsedTime != this.transitionEndTime) {
      return;
    }

    this.transitionEndProperty = null;
    this.transitionEndTime = 0;

    this.menuEl.classList.remove('menu--animatable');
    this.menuEl.removeEventListener('transitionend', this.onTransitionEnd);
  }

  showMenu() {
    this.menuEl.classList.add('menu--animatable');
    this.menuEl.classList.add('menu--visible');
    this.detabinator.inert = false;

    this.transitionEndProperty = 'transform';
    // the duration of transition (make unique to distinguish transitions )
    this.transitionEndTime = 0.33;

    this.menuEl.addEventListener('transitionend', this.onTransitionEnd);
  }

  hideMenu() {
    this.menuEl.classList.add('menu--animatable');
    this.menuEl.classList.remove('menu--visible');
    this.detabinator.inert = true;

    this.transitionEndProperty = 'transform';
    this.transitionEndTime = 0.13;

    this.menuEl.addEventListener('transitionend', this.onTransitionEnd);
  }
}

new Menu();
