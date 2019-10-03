onst DefaultType = {\n    toggle : 'boolean',\n    parent : '(string|element)'\n  }\n\n  const Event = {\n    SHOW           : `show${EVENT_KEY}`,\n    SHOWN          : `shown${EVENT_KEY}`,\n    HIDE           : `hide${EVENT_KEY}`,\n    HIDDEN         : `hidden${EVENT_KEY}`,\n    CLICK_DATA_API : `click${EVENT_KEY}${DATA_API_KEY}`\n  }\n\n  const ClassName = {\n    SHOW       : 'show',\n    COLLAPSE   : 'collapse',\n    COLLAPSING : 'collapsing',\n    COLLAPSED  : 'collapsed'\n  }\n\n  const Dimension = {\n    WIDTH  : 'width',\n    HEIGHT : 'height'\n  }\n\n  const Selector = {\n    ACTIVES     : '.show, .collapsing',\n    DATA_TOGGLE : '[data-toggle=\"collapse\"]'\n  }\n\n  /**\n   * ------------------------------------------------------------------------\n   * Class Definition\n   * ------------------------------------------------------------------------\n   */\n\n  class Collapse {\n    constructor(element, config) {\n      this._isTransitioning = false\n      this._element         = element\n      this._config          = this._getConfig(config)\n      this._triggerArray    = $.makeArray($(\n        `[data-toggle=\"collapse\"][href=\"#${element.id}\"],` +\n        `[data-toggle=\"collapse\"][data-target=\"#${element.id}\"]`\n      ))\n      const tabToggles = $(Selector.DATA_TOGGLE)\n      for (let i = 0; i < tabToggles.length; i++) {\n        const elem = tabToggles[i]\n        const selector = Util.getSelectorFromElement(elem)\n        if (selector !== null && $(selector).filter(element).length > 0) {\n          this._selector = selector\n          this._triggerArray.push(elem)\n        }\n      }\n\n      this._parent = this._config.parent ? this._getParent() : null\n\n      if (!this._config.parent) {\n        this._addAriaAndCollapsedClass(this._element, this._triggerArray)\n      }\n\n      if (this._config.toggle) {\n        this.toggle()\n      }\n    }\n\n    // Getters\n\n    static get VERSION() {\n      return VERSION\n    }\n\n    static get Default() {\n      return Default\n    }\n\n    // Public\n\n    toggle() {\n      if ($(this._element).hasClass(ClassName.SHOW)) {\n        this.hide()\n      } else {\n        this.show()\n      }\n    }\n\n    show() {\n      if (this._isTransitioning ||\n        $(this._element).hasClass(ClassName.SHOW)) {\n        return\n      }\n\n      let actives\n      let activesData\n\n      if (this._parent) {\n        actives = $.makeArray(\n          $(this._parent)\n            .find(Selector.ACTIVES)\n            .filter(`[data-parent=\"${this._config.parent}\"]`)\n        )\n        if (actives.length === 0) {\n          actives = null\n        }\n      }\n\n      if (actives) {\n        activesData = $(actives).not(this._selector).data(DATA_KEY)\n        if (activesData && activesData._isTransitioning) {\n          return\n        }\n      }\n\n      const startEvent = $.Event(Event.SHOW)\n      $(this._element).trigger(startEvent)\n      if (startEvent.isDefaultPrevented()) {\n        return\n      }\n\n      if (actives) {\n        Collapse._jQueryInterface.call($(actives).not(this._selector), 'hide')\n        if (!activesData) {\n          $(actives).data(DATA_KEY, null)\n        }\n      }\n\n      const dimension = this._getDimension()\n\n      $(this._element)\n        .removeClass(ClassName.COLLAPSE)\n        .addClass(ClassName.COLLAPSING)\n\n      this._element.style[dimension] = 0\n\n      if (this._triggerArray.length > 0) {\n        $(this._triggerArray)\n          .removeClass(ClassName.COLLAPSED)\n          .attr('aria-expanded', true)\n      }\n\n      this.setTransitioning(true)\n\n      const complete = () => {\n        $(this._element)\n          .removeClass(ClassName.COLLAPSING)\n          .addClass(ClassName.COLLAPSE)\n          .addClass(ClassName.SHOW)\n\n        this._element.style[dimension] = ''\n\n        this.setTransitioning(false)\n\n        $(this._element).trigger(Event.SHOWN)\n      }\n\n      const capitalizedDimension = dimension[0].toUpperCase() + dimension.slice(1)\n      const scrollSize = `scroll${capitalizedDimension}`\n      const transitionDuration = Util.getTransitionDurationFromElement(this._element)\n\n      $(this._element)\n        .one(Util.TRANSITION_END, complete)\n        .emulateTransitionEnd(transitionDuration)\n\n      this._element.style[dimension] = `${this._element[scrollSize]}px`\n    }\n\n    hide() {\n      if (this._isTransitioning ||\n        !$(this._element).hasClass(ClassName.SHOW)) {\n        return\n      }\n\n      const startEvent = $.Event(Event.HIDE)\n      $(this._element).trigger(startEvent)\n      if (startEvent.isDefaultPrevented()) {\n        return\n      }\n\n      const dimension = this._getDimension()\n\n      this._element.style[dimension] = `${this._element.getBoundingClientRect()[dimension]}px`\n\n      Util.reflow(this._element)\n\n      $(this._element)\n        .addClass(ClassName.COLLAPSING)\n        .removeClass(ClassName.COLLAPSE)\n        .removeClass(ClassName.SHOW)\n\n      if (this._triggerArray.length > 0) {\n        for (let i = 0; i < this._triggerArray.length; i++) {\n          const trigger = this._triggerArray[i]\n          const selector = Util.getSelectorFromElement(trigger)\n          if (selector !== null) {\n            const $elem = $(selector)\n            if (!$elem.hasClass(ClassName.SHOW)) {\n              $(trigger).addClass(ClassName.COLLAPSED)\n                .attr('aria-expanded', false)\n            }\n          }\n        }\n      }\n\n      this.setTransitioning(true)\n\n      const complete = () => {\n        this.setTransitioning(false)\n        $(this._element)\n          .removeClass(ClassName.COLLAPSING)\n          .addClass(ClassName.COLLAPSE)\n          .trigger(Event.HIDDEN)\n      }\n\n      this._element.style[dimension] = ''\n      const transitionDuration = Util.getTransitionDurationFromElement(this._element)\n\n      $(this._element)\n        .one(Util.TRANSITION_END, complete)\n        .emulateTransitionEnd(transitionDuration)\n    }\n\n    setTransitioning(isTransitioning) {\n      this._isTransitioning = isTransitioning\n    }\n\n    dispose() {\n      $.removeData(this._element, DATA_KEY)\n\n      this._config          = null\n      this._parent          = null\n      this._element         = null\n      this._triggerArray    = null\n      this._isTransitioning = null\n    }\n\n    // Private\n\n    _getConfig(config) {\n      config = {\n        ...Default,\n        ...config\n      }\n      config.toggle = Boolean(config.toggle) // Coerce string values\n      Util.typeCheckConfig(NAME, config, DefaultType)\n      return config\n    }\n\n    _getDimension() {\n      const hasWidth = $(this._element).hasClass(Dimension.WIDTH)\n      return hasWidth ? Dimension.WIDTH : Dimension.HEIGHT\n    }\n\n    _getParent() {\n      let parent = null\n      if (Util.isElement(this._config.parent)) {\n        parent = this._config.parent\n\n        // It's a jQuery object\n        if (typeof this._config.parent.jquery !== 'undefined') {\n          parent = this._config.parent[0]\n        }\n      } else {\n        parent = $(this._config.parent)[0]\n      }\n\n      const selector =\n        `[data-toggle=\"collapse\"][data-parent=\"${this._config.parent}\"]`\n\n      $(parent).find(selector).each((i, element) => {\n        this._addAriaAndCollapsedClass(\n          Collapse._getTargetFromElement(element),\n          [element]\n        )\n      })\n\n      return parent\n    }\n\n    _addAriaAndCollapsedClass(element, triggerArray) {\n      if (element) {\n        const isOpen = $(element).hasClass(ClassName.SHOW)\n\n        if (triggerArray.length > 0) {\n          $(triggerArray)\n            .toggleClass(ClassName.COLLAPSED, !isOpen)\n            .attr('aria-expanded', isOpen)\n        }\n      }\n    }\n\n    // Static\n\n    static _getTargetFromElement(element) {\n      const selector = Util.getSelectorFromElement(element)\n      return selector ? $(selector)[0] : null\n    }\n\n    static _jQueryInterface(config) {\n      return this.each(function () {\n        const $this   = $(this)\n        let data      = $this.data(DATA_KEY)\n        const _config = {\n          ...Default,\n          ...$this.data(),\n          ...typeof config === 'object' && config\n        }\n\n        if (!data && _config.toggle && /show|hide/.test(config)) {\n          _config.toggle = false\n        }\n\n        if (!data) {\n          data = new Collapse(this, _config)\n          $this.data(DATA_KEY, data)\n        }\n\n        if (typeof config === 'string') {\n          if (typeof data[config] === 'undefined') {\n            throw new TypeError(`No method named \"${config}\"`)\n          }\n          data[config]()\n        }\n      })\n    }\n  }\n\n  /**\n   * ------------------------------------------------------------------------\n   * Data Api implementation\n   * ------------------------------------------------------------------------\n   */\n\n  $(document).on(Event.CLICK_DATA_API, Selector.DATA_TOGGLE, function (event) {\n    // preventDefault only for <a> elements (which change the URL) not inside the collapsible element\n    if (event.currentTarget.tagName === 'A') {\n      event.preventDefault()\n    }\n\n    const $trigger = $(this)\n    const selector = Util.getSelectorFromElement(this)\n    $(selector).each(function () {\n      const $target = $(this)\n      const data    = $target.data(DATA_KEY)\n      const config  = data ? 'toggle' : $trigger.data()\n      Collapse._jQueryInterface.call($target, config)\n    })\n  })\n\n  /**\n   * ------------------------------------------------------------------------\n   * jQuery\n   * ------------------------------------------------------------------------\n   */\n\n  $.fn[NAME] = Collapse._jQueryInterface\n  $.fn[NAME].Constructor = Collapse\n  $.fn[NAME].noConflict = function () {\n    $.fn[NAME] = JQUERY_NO_CONFLICT\n    return Collapse._jQueryInterface\n  }\n\n  return Collapse\n})($)\n\nexport default Collapse\n","import $ from 'jquery'\nimport Popper from 'popper.js'\nimport Util from './util'\n\n/**\n * --------------------------------------------------------------------------\n * Bootstrap (v4.1.0): dropdown.js\n * Licensed under MIT (https://github.com/twbs/bootstrap/blob/master/LICENSE)\n * --------------------------------------------------------------------------\n */\n\nconst Dropdown = (($) => {\n  /**\n   * ------------------------------------------------------------------------\n   * Constants\n   * ------------------------------------------------