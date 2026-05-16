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

Each processor is `(fn [event] event-or-nil)`. Run in order, before fan-out
to appenders. Returning `nil` short-circuits the chain and drops the event
before any appender runs — ideal for sampling and rate-limiting.

Stock processors:

```phel
;; keep 5% of events
(log/sampler 0.05)

;; sample only at-or-below :debug; :info+ always pass
(log/level-sampler :debug 0.1)

;; at most 100 events per second per :ns (override key via :key-fn)
(log/rate-limiter 100)
(log/rate-limiter 5 {:key-fn #(get % :message)})
```

Custom example:

```phel
(defn mask-secrets [event]
  (let [masked (-> (get event :data {})
                   (assoc :token "<redacted>"))]
    (assoc event :data masked)))

(log/update-config! {:processors [mask-secrets (log/rate-limiter 50)]})
```

## `:base-data`

Merged into every event's `:data` map. Per-call data overrides keys here.

```phel
(log/update-config! {:base-data {:app "my-app"}})
(log/info "hi" {:user 42})
;; event :data => {:app "my-app" :user 42}
```
