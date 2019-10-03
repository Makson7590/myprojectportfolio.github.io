hod.POSITION\n\n      const offsetMethod = this._config.method === 'auto'\n        ? autoMethod : this._config.method\n\n      const offsetBase = offsetMethod === OffsetMethod.POSITION\n        ? this._getScrollTop() : 0\n\n      this._offsets = []\n      this._targets = []\n\n      this._scrollHeight = this._getScrollHeight()\n\n      const targets = $.makeArray($(this._selector))\n\n      targets\n        .map((element) => {\n          let target\n          const targetSelector = Util.getSelectorFromElement(element)\n\n          if (targetSelector) {\n            target = $(targetSelector)[0]\n          }\n\n          if (target) {\n            const targetBCR = target.getBoundingClientRect()\n            if (targetBCR.width || targetBCR.height) {\n              // TODO (fat): remove sketch reliance on jQuery position/offset\n              return [\n                $(target)[offsetMethod]().top + offsetBase,\n                targetSelector\n              ]\n            }\n          }\n          return null\n        })\n        .filter((item) => item)\n        .sort((a, b) => a[0] - b[0])\n        .forEach((item) => {\n          this._offsets.push(item[0])\n          this._targets.push(item[1])\n        })\n    }\n\n    dispose() {\n      $.removeData(this._element, DATA_KEY)\n      $(this._scrollElement).off(EVENT_KEY)\n\n      this._element       = null\n      this._scrollElement = null\n      this._config        = null\n      this._selector      = null\n      this._offsets       = null\n      this._targets       = null\n      this._activeTarget  = null\n      this._scrollHeight  = null\n    }\n\n    // Private\n\n    _getConfig(config) {\n      config = {\n        ...Default,\n        ...config\n      }\n\n      if (typeof config.target !== 'string') {\n        let id = $(config.target).attr('id')\n        if (!id) {\n          id = Util.getUID(NAME)\n          $(config.target).attr('id', id)\n        }\n        config.target = `#${id}`\n      }\n\n      Util.typeCheckConfig(NAME, config, DefaultType)\n\n      return config\n    }\n\n    _getScrollTop() {\n      return this._scrollElement === window\n        ? this._scrollElement.pageYOffset : this._scrollElement.scrollTop\n    }\n\n    _getScrollHeight() {\n      return this._scrollElement.scrollHeight || Math.max(\n        document.body.scrollHeight,\n        document.documentElement.scrollHeight\n      )\n    }\n\n    _getOffsetHeight() {\n      return this._scrollElement === window\n        ? window.innerHeight : this._scrollElement.getBoundingClientRect().height\n    }\n\n    _process() {\n      const scrollTop    = this._getScrollTop() + this._config.offset\n      const scrollHeight = this._getScrollHeight()\n      const maxScroll    = this._config.offset +\n        scrollHeight -\n        this._getOffsetHeight()\n\n      if (this._scrollHeight !== scrollHeight) {\n        this.refresh()\n      }\n\n      if (scrollTop >= maxScroll) {\n        const target = this._targets[this._targets.length - 1]\n\n        if (this._activeTarget !== target) {\n          this._activate(target)\n        }\n        return\n      }\n\n      if (this._activeTarget && scrollTop < this._offsets[0] && this._offsets[0] > 0) {\n        this._activeTarget = null\n        this._clear()\n        return\n      }\n\n      for (let i = this._offsets.length; i--;) {\n        const isActiveTarget = this._activeTarget !== this._targets[i] &&\n            scrollTop >= this._offsets[i] &&\n            (typeof this._offsets[i + 1] === 'undefined' ||\n                scrollTop < this._offsets[i + 1])\n\n        if (isActiveTarget) {\n          this._activate(this._targets