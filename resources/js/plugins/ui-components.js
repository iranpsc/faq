// Global UI Components Plugin
import * as components from '../components/ui'

export default {
  install(app) {
    // Register all UI components globally
    Object.keys(components).forEach(name => {
      if (name !== 'default') {
        app.component(name, components[name])
      }
    })
  }
}
