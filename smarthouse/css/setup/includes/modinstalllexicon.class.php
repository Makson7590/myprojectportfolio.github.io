 TRANSITION_END = 'transitionend'\n  const MAX_UID = 1000000\n  const MILLISECONDS_MULTIPLIER = 1000\n\n  // Shoutout AngusCroll (https://goo.gl/pxwQGp)\n  function toType(obj) {\n    return {}.toString.call(obj).match(/\\s([a-z]+)/i)[1].toLowerCase()\n  }\n\n  function getSpecialTransitionEndEvent() {\n    return {\n      bindType: TRANSITION_END,\n      delegateType: TRANSITION_END,\n      handle(event) {\n        if ($(event.target).is(this)) {\n          return event.handleObj.handler.apply(this, arguments) // eslint-disable-line prefer-rest-params\n        }\n        return undefined // eslint-disable-line no-undefined\n      }\n    }\n  }\n\n  function transitionEndEmulator(duration) {\n    let called = false\n\n    $(this).one(Util.TRANSITION_END, () => {\n      called = true\n    })\n\n    setTimeout(() => {\n      if (!called) {\n        Util.triggerTransitionEnd(this)\n      }\n    }, duration)\n\n    return this\n  }\n\n  function setTransitionEndSupport() {\n    $.fn.emulateTransitionEnd = transitionEndEmulator\n    $.event.special[Util.TRANSITION_END] = getSpecialTransitionEndEvent()\n  }\n\n  /**\n   * --------------------------------------------------------------------------\n   * Public Util Api\n   * --------------------------------------------------------------------------\n   */\n\n  const Util = {\n\n    TRANSITION_END: 'bsTransitionEnd',\n\n    getUID(prefix) {\n      do {\n        // eslint-disable-next-line no-bitwise\n        prefix += ~~(Math.random() * MAX_UID) // \"~~\" acts like a faster Math.floor() here\n      } while (document.getElementById(prefix))\n      return prefix\n    },\n\n    getSelectorFromElement(element) {\n      let selector = element.getAttribute('data-target')\n      if (!selector || selector === '#') {\n        selector = element.getAttribute('href') || ''\n      }\n\n      try {\n        const $selector = $(document).find(selector)\n        return $selector.length > 0 ? selector : null\n      } catch (err) {\n        return null\n      }\n    },\n\n    getTransitionDurationFromElement(element) {\n      if (!element) {\n        return 0\n      }\n\n      // Get transition-duration of the element\n      let transitionDuration = $(element).css('transition-duration')\n      const floatTransitionDuration = parseFloat(transitionDuration)\n\n      // Return 0 if element or transition duration is not found\n      if (!floatTransitionDuration) {\n        return 0\n      }\n\n      // If multiple durations are defined, take the first\n      transitionDuration = transitionDuration.split(',')[0]\n\n      return parseFloat(transitionDuration) * MILLISECONDS_MULTIPLIER\n    },\n\n    reflow(element) {\n      return element.offsetHeight\n    },\n\n    triggerTransitionEnd(element) {\n      $(element).trigger(TRANSITION_END)\n    },\n\n    // TODO: Remove in v5\n    supportsTransitionEnd() {\n      return Boolean(TRANSITION_END)\n    },\n\n    isElement(obj) {\n      return (obj[0] || obj).nodeType\n    },\n\n    typeCheckConfig(componentName, config, configTypes) {\n      for (const property in configTypes) {\n        if (Object.prototype.hasOwnProperty.call(configTypes, property)) {\n          const expectedTypes = configTypes[property]\n          const value         = config[property]\n          const valueType     = value && Util.isElement(value)\n            ? 'element' : toType(value)\n\n          if (!new RegExp(expectedTypes).test(valueType)) {\n            throw new Error(\n              `${componentName.toUpperCase()}: ` +\n              `Option \"${property}\" provided type \"${valueType}\" ` +\n              `but expected type \"${expectedTypes}\".`)\n          }\n        }\n      }\n    }\n  }\n\n  setTransitionEndSupport()\n\n  return Util\n})($)\n\nexport default Util\n","import $ from 'jquery'\nimport Util from './util'\n\n/**\n * --------------------------------------------------------------------------\n * Bootstrap (v4.1.0): alert.js\n * Licensed under MIT (https://github.com/twbs/bootstrap/blob/master/LICENSE)\n * --------------------------------------------------------------------------\n */\n\nconst Alert = (($) => {\n  /**\n   * ------------------------------------------------------------------------\n   * Constants\n   * ------------------------------------------------------------------------\n   */\n\n  const NAME                = 'alert'\n  const VERSION             = '4.1.0'\n  const DATA_KEY            = 'bs.alert'\n  const EVENT_KEY           = `.${DATA_KEY}`\n  const DATA_API_KEY        = '.data-api'\n  const JQUERY_NO_CONFLICT  = $.fn[NAME]\n\n  const Selector = {\n    DISMISS : '[data-dismiss=\"alert\"]'\n  }\n\n  const Event = {\n    CLOSE          : `close${EVENT_KEY}`,\n    CLOSED         : `closed${EVENT_KEY}`,\n    CLICK_DATA_API : `click${EVENT_KEY}${DATA_API_KEY}`\n  }\n\n  const ClassName = {\n    ALERT : 'alert',\n    FADE  : 'fade',\n    SHOW  : 'show'\n  }\n\n  /**\n   * ------------------------------------------------------------------------\n   * Class Definition\n   * ------------------------------------------------------------------------\n   */\n\n  class Alert {\n    constructor(element) {\n      this._element = element\n    }\n\n    // Getters\n\n    static get VERSION() {\n      return VERSION\n    }\n\n    // Public\n\n    close(element) {\n      element = element || this._element\n\n      const rootElement = this._getRootElement(element)\n      const customEvent = this._triggerCloseEvent(rootElement)\n\n      if (customEvent.isDefaultPrevented()) {\n        return\n      }\n\n      this._removeElement(rootElement)\n    }\n\n    dispose() {\n      $.removeData(this._element, DATA_KEY)\n      this._element = null\n    }\n\n    // Private\n\n    _getRootElement(element) {\n      const selector = Util.getSelectorFromElement(element)\n      let parent     = false\n\n      if (selector) {\n        parent = $(selector)[0]\n      }\n\n      if (!parent) {\n        parent = $(element).closest(`.${ClassName.ALERT}`)[0]\n      }\n\n      return parent\n    }\n\n    _triggerCloseEvent(element) {\n      const closeEvent = $.Event(Event.CLOSE)\n\n      $(element).trigger(closeEvent)\n      return closeEvent\n    }\n\n    _removeElement(element) {\n      $(element).removeClass(ClassName.SHOW)\n\n      if (!$(element).hasClass(ClassName.FADE)) {\n        this._destroyElement(element)\n        return\n      }\n\n      const transitionDuration = Util.getTransitionDurationFromElement(element)\n\n      $(element)\n        .one(Util.TRANSITION_END, (event) => this._destroyElement(element, event))\n        .emulateTransitionEnd(transitionDuration)\n    }\n\n  