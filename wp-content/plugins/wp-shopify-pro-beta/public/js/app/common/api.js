import { getComponentOptions, graphQuery, maybeAlterErrorMessage } from '/Users/andrew/www/devil/devilbox-new/data/www/wpshopify-api'
import { getClientComponentsOptions } from './data'
import { getAvailableCartButtons, getAvailableSearch, getAvailableStorefront, getAvailableCollections, getAvailableProducts } from './dom'
import find from 'lodash/find'
import isObject from 'lodash/isObject'
import isArray from 'lodash/isArray'
import to from 'await-to-js'
import last from 'lodash/last'
import isNaN from 'lodash/isNaN'
import isEmpty from 'lodash/isEmpty'
import has from 'lodash/has'
import assign from 'lodash/assign'
import extend from 'lodash/extend'
import concat from 'lodash/concat'
import groupBy from 'lodash/groupBy'

function toCamel(s) {
   return s.replace(/([-_][a-z])/gi, $1 => {
      return $1
         .toUpperCase()
         .replace('-', '')
         .replace('_', '')
   })
}

function underscoreToCamel(o) {
   if (isArray(o)) {
      return o
   }

   if (isObject(o)) {
      const n = {}

      Object.keys(o).forEach(k => {
         n[toCamel(k)] = underscoreToCamel(o[k])
      })

      return n
   }

   return o
}

function returnModelFromResponse(shopifyResponse, type) {
   if (isEmpty(shopifyResponse.model)) {
      return false
   }

   return shopifyResponse.model[type]
}

function getComponentOptionsFromResponse(componentsResponse) {
   return componentsResponse.componentOptions
}

function hasAPIErrors(shopifyResponse) {
   return has(shopifyResponse, 'errors')
}

function findDataOfType(data, type) {
   return find(data, type)[type]
}

function getQueryFromComponentOptions(queryParams, componentOptions) {
   if (isEmpty(queryParams)) {
      return false
   }

   return queryParams.query
}

function getPageSizeFromComponentOptions(queryParams, componentOptions, isConnectionParams = false) {
   let pageSize = parseInt(queryParams.page_size)

   if (!componentOptions.limit) {
      return pageSize
   }

   let limit = parseInt(componentOptions.limit)

   if (isNaN(limit)) {
      return false
   }

   if (isEmpty(queryParams)) {
      return false
   }

   if (!queryParams.page_size) {
      return false
   }

   // If single is passed in, only show 1
   if (has(componentOptions, 'single') && componentOptions.single && !isConnectionParams) {
      return 1
   }

   if (!limit) {
      return pageSize
   } else {
      if (limit <= pageSize) {
         return limit
      } else {
         return pageSize
      }
   }
}

function getSortKeyFromComponentOptions(queryParams, componentOptions) {
   if (isEmpty(queryParams)) {
      return false
   }

   if (!has(queryParams, 'sort_by')) {
      return false
   }

   if (!queryParams.sort_by) {
      return false
   }

   return queryParams.sort_by.toUpperCase()
}

function getReverseFromComponentOptions(queryParams, componentOptions) {
   if (isEmpty(queryParams)) {
      return false
   }

   return queryParams.reverse
}

function componentQueryParams(queryParams, componentOptions, isConnectionParams = false) {
   return {
      first: getPageSizeFromComponentOptions(queryParams, componentOptions, isConnectionParams),
      query: getQueryFromComponentOptions(queryParams, componentOptions),
      reverse: getReverseFromComponentOptions(queryParams, componentOptions),
      sortKey: getSortKeyFromComponentOptions(queryParams, componentOptions)
   }
}

function hasValidQuery(queryParams) {
   if (!queryParams) {
      return false
   }

   if (!has(queryParams, 'first') || !has(queryParams, 'sortKey')) {
      return false
   }

   if (!queryParams.first || !queryParams.sortKey) {
      return false
   }

   return true
}

function setComponentOptionsToComponent(allComponentOptions, componentOptions) {
   return allComponentOptions.map(component => {
      var foundComponent = find(componentOptions, { componentId: component.componentId })

      if (foundComponent) {
         return extend({ componentOptions: foundComponent.componentOptions }, component)
      }
   })
}

function getUniqueProductComponentOptions() {
   return getClientComponentsOptions(getAvailableProducts())
}

function getUniqueCollectionComponentOptions() {
   return getClientComponentsOptions(getAvailableCollections())
}

function getUniqueCartComponentOptions() {
   return getClientComponentsOptions(getAvailableCartButtons())
}

function getUniqueSearchComponentOptions() {
   return getClientComponentsOptions(getAvailableSearch())
}

function getUniqueStorefrontComponentOptions() {
   return getClientComponentsOptions(getAvailableStorefront())
}

function convertComponentsToCamelCase(components) {
   return components.map(component => {
      component.componentOptions = assign(component.componentOptions, underscoreToCamel(component.componentOptions.componentOptions))

      return component
   })
}

function addParamsToComponentOptions(components) {
   return components.map(component => {
      return extend(
         {
            componentQueryParams: componentQueryParams(component.componentOptions.componentQueryParams, component.componentOptions),
            componentConnectionParams: componentQueryParams(component.componentOptions.componentConnectionParams, component.componentOptions, true)
         },
         component
      )
   })
}

function addPayloadToComponentOptions(component, shopifyResponse, index) {
   var payload = isEmpty(shopifyResponse[index]) ? [] : shopifyResponse[index].model[component.componentOptions.dataType]

   let lastCursor = false

   if (!isEmpty(shopifyResponse[index])) {
      let edges = shopifyResponse[index].data[component.componentOptions.dataType].edges

      if (isEmpty(edges)) {
         lastCursor = false
      } else {
         lastCursor = last(edges).cursor
      }
   }

   return extend(
      {
         componentPayload: payload,
         componentPayloadLastCursor: lastCursor
      },
      component
   )
}

function groupByComponentType(components) {
   return groupBy(components, 'componentType')
}

function createQueryPromises(components) {
   if (!isArray(components)) {
      return [Promise.reject('It looks like the initial query data for createQueryPromises is not an array.')]
   }

   return components.map(async component => {
      /* 

      At the moment, the only time it should land here is when we show 
      the cart component. Since the cart component doesn't call Shopify, we want to 
      short circuit and just continue on.
      
      */
      if (!hasValidQuery(component.componentQueryParams)) {
         return Promise.resolve(false)
      }

      if (has(component.componentOptions, 'componentConnectionParams')) {
         var connectionParams = component.componentConnectionParams
      } else {
         var connectionParams = false
      }

      return graphQuery(component.componentOptions.dataType, component.componentQueryParams, connectionParams)
   })
}

/*

Returns a Promise

*/
function getComponentUserOptions() {
   return new Promise(async (resolve, reject) => {
      var allComponentOptions = concat(
         getUniqueProductComponentOptions(),
         getUniqueCollectionComponentOptions(),
         getUniqueCartComponentOptions(),
         getUniqueSearchComponentOptions(),
         getUniqueStorefrontComponentOptions()
      )

      // Calls our server (grabs component params used by shortcodes + render api)
      const [cError, componentOptions] = await to(getComponentOptions(allComponentOptions))

      if (cError) {
         return reject(maybeAlterErrorMessage(cError))
      }

      if (!componentOptions) {
         return reject('No component options found')
      }

      var componentsWithParams = addParamsToComponentOptions(convertComponentsToCamelCase(setComponentOptionsToComponent(allComponentOptions, componentOptions)))

      const queryPromises = createQueryPromises(componentsWithParams)

      const [shopifyError, shopifyResponse] = await to(Promise.all(queryPromises))

      if (shopifyError) {
         return reject(maybeAlterErrorMessage(shopifyError))
      }

      if (!shopifyResponse) {
         return reject(maybeAlterErrorMessage('The initial Shopify response was empty for an unknown reason.'))
      }

      var modData = componentsWithParams.map((component, index) => {
         return addPayloadToComponentOptions(component, shopifyResponse, index)
      })

      var finalStuff = groupByComponentType(modData)

      resolve(finalStuff)
   })
}

export { getComponentUserOptions, hasAPIErrors }
