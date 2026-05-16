# Config

`phel.log` keeps one global config atom. Three entry points:

| Function           | Effect                                              |
|--------------------|-----------------------------------------------------|
| `get-config`       | snapshot current map                                |
| `set-config!`      | replace whole map                                   |
| `update-config!`   | `merge` partial map into current                    |
| `reset-config!`    | restore default (mainly for tests)                  |

## Shape

```phel
{:min-level :info                          ; level keyword
 :appenders [<appender-map> ...]           ; fan-out targets
 :ns-filter {:allow [<pattern> ...]        ; optional
             :deny  [<pattern> ...]}       ; optional, wins over :allow
 :processors [(fn [event] event) ...]      ; middleware, left-to-right
 :base-data  {:app "my-app" :env "prod"}}  ; merged into every event's :data
```

## `:min-level`

Global threshold. Events below this level are dropped before any appender
runs. Per-appender `:min-level` can raise (never lower) the threshold for one
target.

## `:ns-filter` patterns

Wildcards apply to the call-site namespace string.

| Pattern         | Matches                                                          |
|-----------------|------------------------------------------------------------------|
| `my-app.users`  | exact namespace                                                  |
| `my-app.*`      | one extra segment: `my-app.users`, `my-app.api` (not deeper)     |
| `my-app.**`     | any depth: `my-app`, `my-app.api`, `my-app.api.v2.controllers`   |

`:deny` wins over `:allow`. Empty / missing `:allow` is treated as allow-all.

## `:processors`

Each processor is `(fn [event] event)`. Run in order, before fan-out to
appenders. Use for: masking secrets, attaching trace IDs, sampling, dropping
events (`(fn [ev] (if (drop? ev) nil ev))` — `nil` is currently still
written; filter via `:ns-filter` or per-appender `:min-level` instead).

## `:base-data`

Merged into every event's `:data` map. Per-call data overrides keys here.

```phel
(log/update-config! {:base-data {:app "my-app"}})
(log/info "hi" {:user 42})
;; event :data => {:app "my-app" :user 42}
```
