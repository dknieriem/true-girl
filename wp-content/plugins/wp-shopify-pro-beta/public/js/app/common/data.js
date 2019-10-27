function getClientComponentsOptions(elements) {
   if (!elements) {
      return []
   }

   return Array.prototype.map.call(elements, function(element) {
      return {
         componentElement: element ? element : false,
         componentId: element.dataset.wpsComponentOptionsId ? element.dataset.wpsComponentOptionsId : false,
         componentType: element.dataset.wpsClientComponentType ? element.dataset.wpsClientComponentType : false
      }
   })
}

export { getClientComponentsOptions }
