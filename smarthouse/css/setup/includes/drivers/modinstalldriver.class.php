ect containing the offsets which will be applied to the popper\n */\nfunction getPopperOffsets(popper, referenceOffsets, placement) {\n  placement = placement.split('-')[0];\n\n  // Get popper node sizes\n  var popperRect = getOuterSizes(popper);\n\n  // Add position, width and height to our offsets object\n  var popperOffsets = {\n    width: popperRect.width,\n    height: popperRect.height\n  };\n\n  // depending by the popper placement we have to compute its offsets slightly differently\n  var isHoriz = ['right', 'left'].indexOf(placement) !== -1;\n  var mainSide = isHoriz ? 'top' : 'left';\n  var secondarySide = isHoriz ? 'left' : 'top';\n  var measurement = isHoriz ? 'height' : 'width';\n  var secondaryMeasurement = !isHoriz ? 'height' : 'width';\n\n  popperOffsets[mainSide] = referenceOffsets[mainSide] + referenceOffsets[measurement] / 2 - popperRect[measurement] / 2;\n  if (placement === secondarySide) {\n    popperOffsets[secondarySide] = referenceOffsets[secondarySide] - popperRect[secondaryMeasurement];\n  } else {\n    popperOffsets[secondarySide] = referenceOffsets[getOppositePlacement(secondarySide)];\n  }\n\n  return popperOffsets;\n}\n\n/**\n * Mimics the `find` method of Array\n * @method\n * @memberof Popper.Utils\n * @argument {Array} arr\n * @argument prop\n * @argument value\n * @returns index or -1\n */\nfunction find(arr, check) {\n  // use native find if supported\n  if (Array.prototype.find) {\n    return arr.find(check);\n  }\n\n  // use `filter` to obtain the same behavior of `find`\n  return arr.filter(check)[0];\n}\n\n/**\n * Return the index of the matching object\n * @method\n * @memberof Popper.Utils\n * @argument {Array} arr\n * @argument prop\n * @argument value\n * @returns index or -1\n */\nfunction findIndex(arr, prop, value) {\n  // use native findIndex if supported\n  if (Array.prototype.findIndex) {\n    return arr.findIndex(function (cur) {\n      return cur[prop] === value;\n    });\n  }\n\n  // use `find` + `indexOf` if `findIndex` isn't supported\n  var match = find(arr, function (obj) {\n    return obj[prop] === value;\n  });\n  return arr.indexOf(match);\n}\n\n/**\n * Loop trough the list of modifiers and run them in order,\n * each of them will then edit the data object.\n * @method\n * @memberof Popper.Utils\n * @param {dataObject} data\n * @param {Array} modifiers\n * @param {String} ends - Optional modifier name used as stopper\n * @returns {dataObject}\n */\nfunction runModifiers(modifiers, data, ends) {\n  var modifiersToRun = ends === undefined ? modifiers : modifiers.slice(0, findIndex(modifiers, 'name', ends));\n\n  modifiersToRun.forEach(function (modifier) {\n    if (modifier['function']) {\n      // eslint-disable-line dot-notation\n      console.warn('`modifier.function` is deprecated, use `modifier.fn`!');\n    }\n    var fn = modifier['function'] || modifier.fn; // eslint-disable-line dot-notation\n    if (modifier.enabled && isFunction(fn)) {\n      // Add properties to offsets to make them a complete clientRect object\n      // we do this before each modifier to make sure the previous one doesn't\n      // mess with these values\n      data.offsets.popper = getClientRect(data.offsets.popper);\n      data.offsets.reference = getClientRect(data.offsets.reference);\n\n      data = fn(data, modifier);\n    }\n  });\n\n  return data;\n}\n\n/**\n * Updates the position of the popper, computing the new offsets and applying\n * the new style.<br />\n * Prefer `scheduleUpdate` over `update` because of performance reasons.\n * @method\n * @memberof Popper\n */\nfunction update() {\n  // if popper is destroyed, don't perform any further update\n  if (this.state.isDestroyed) {\