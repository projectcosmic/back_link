# Back Link

Add links to pages that point to the previous page or a fallback default if the
previous page is not evaluated to be of the same website (matched against
`$settings['trusted_host_patterns']` if set).

## Recommended setup

### Disable Page Cache core module

The links vary with the `referer` HTTP header. With granular variation,
Page Cache would be unsuitable to cache pages with back links in them.

### Add `headers:referer` auto-placeholder condition

Again, since links will vary by `referer` HTTP header, it is recommended to add
this as a cache context that will auto-placeholder the rendered link content.
In a `services.yml`:

```yml
parameters:
  renderer.config:
    auto_placeholder_conditions:
      contexts: ['headers:referer', 'session', 'user']
```
