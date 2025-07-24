// Rules for LLMs when writing Laravel code:

1. Always follow PSR-12 coding standards and Laravel's own conventions for file and folder structure.
2. Use Eloquent ORM for database interactions unless raw queries are explicitly required.
3. Validate all user input using Laravel's built-in validation mechanisms (e.g., `$request->validate()` or Form Requests).
4. Never expose sensitive information (such as passwords, API keys, or environment variables) in code or error messages.
5. Use route model binding where possible for cleaner and safer controller methods.
6. Prefer dependency injection over facades in controllers and services.
7. Use migrations for all database schema changes.
8. Write clear, descriptive comments and PHPDoc blocks for classes, methods, and properties.
9. Use resource controllers and route resources for RESTful APIs.
10. Always sanitize and escape output in Blade templates to prevent XSS attacks.
11. Use Laravel's built-in authentication and authorization features for user access control.
12. Avoid using `dd()` or `dump()` in production code.
13. Use configuration files and environment variables for settings, not hardcoded values.
14. Write unit and feature tests for new functionality using Laravel's testing tools.
15. Follow naming conventions: controllers in `StudlyCase`, models in `StudlyCase`, variables and methods in `camelCase`, database tables in `snake_case`.
16. Use queues and jobs for long-running or asynchronous tasks.
17. Handle exceptions gracefully and use Laravel's exception handling features.
18. Use service providers for binding classes into the service container.
19. Keep controllers thin; move business logic to service classes or models.
20. Document any artisan commands or custom scripts created.
21. Use Artisan command to generate models, controllers, migrations, etc.
22. Add comments to complex logic or algorithms to explain their purpose.
23. Add comments before model properties and relationships. 

These rules ensure maintainable, secure, and idiomatic Laravel code.

#Vuejs Rules:
1. Use single-file components (SFCs) with `.vue` extension for better organization.
2. Follow Vue's official style guide for naming conventions and code structure.
3. Use `v-bind` and `v-on` directives for binding data and events, respectively, to keep templates clean.
4. Prefer using `v-if` for conditional rendering and `v-for` for lists, ensuring keys are used for list items.
5. Use computed properties for derived state and methods for actions, keeping templates declarative.
6. Use Vue's reactivity system effectively by avoiding direct mutations of props; instead, emit events to parent components.
7. Use scoped styles in single-file components to avoid CSS conflicts.
8. Use Vue Router for navigation and Vuex for state management in larger applications.
9. Use `v-model` for two-way data binding on form inputs, but avoid using it on complex components.
10. Use slots for content distribution in components, allowing for flexible component composition.
11. Use lifecycle hooks (`created`, `mounted`, etc.) to manage component behavior, but avoid heavy logic in them.
12. Use `async` and `await` for asynchronous operations in methods to keep code readable.
13. Use `v-show` for toggling visibility without removing elements from the DOM, but prefer `v-if` for conditional rendering.
14. Use `v-once` for static content that does not change, improving performance.
15. Use `key` attribute in lists to help Vue track elements and optimize rendering.
16. Use `provide` and `inject` for dependency injection in deeply nested components.
17. Use `watch` for observing changes in reactive data and performing side effects.
18. Use `ref` for accessing DOM elements or child components directly when necessary.
19. Use `defineProps` and `defineEmits` in the `<script setup>` to declare component props and events.
20. Use sweet alerts for resource deletion confirmations and other user interactions to enhance UX.
