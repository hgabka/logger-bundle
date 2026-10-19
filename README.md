# hgabka/logger-bundle

Háromféle naplózás Symfony alkalmazásokhoz, adatbázisba (Doctrine) és/vagy fájlba:

1. **Kivétel-naplózás és email értesítés** – a nem kezelt kivételeket menti, és (opcionálisan) emailt küld róluk; az ismétlődő hibákat hash alapján összevonja és számolja.
2. **Akció-naplózás** – felhasználói műveletek (pl. „rendelés módosítva”) rögzítése a kontrollerekből.
3. **Oszlop-naplózás** – a `LoggableEntityInterface`-t megvalósító entitások mezőinek változása (régi/új érték) minden insert/update/delete esetén.

## Telepítés

```bash
composer require hgabka/logger-bundle
```

```php
// config/bundles.php
Hgabka\LoggerBundle\HgabkaLoggerBundle::class => ['all' => true],
```

A bundle a `hgabka/utils-bundle` `hgabka_utils.hgabka_utils` szolgáltatását, a Mailert és a Doctrine-t használja. A táblákat Doctrine migrációval kell létrehozni: `hg_logger_log_action`, `hg_logger_log_column`, `hg_logger_notify`, `hg_logger_notify_call`. Az entitások `hg_utils_longblob` típust használnak (a utils-bundle regisztrálja).

## Konfiguráció

```yaml
# config/packages/hgabka_logger.yaml
hgabka_logger:
    notifier:
        mails:
            enabled: prod              # always | prod | debug | never
            from_mail: info@hgabka.eu
            from_name: hgLoggerBundle
            subject: 'Exception a(z) [host] oldalon'
            recipients: [dev@example.com]
            send_only_if_new: false    # csak új (nem ismétlődő) hibáról küldjön levelet
            send_404: true
        logging:
            enabled: prod              # always | prod | debug | never
            type:                      # hova naplózzon: file | database | both | none
                debug: none
                prod: both
            log_path: '%kernel.logs_dir%/exception'
            log_404: true
    action_logger:
        enabled: prod
        common_identifier: symfony     # az `ident` mező értéke a rekordokban
        translation_domain: logger
    column_logger:
        enabled: prod
        common_identifier: symfony
        translation_domain: logger
```

## Használat

### Kivételek

Nincs teendő: az `ExceptionListener` a `kernel.exception` eseményre feliratkozik, és az `ExceptionNotifier::trigger()` a beállítások szerint fájlba (`ExceptionLogger`, `ExceptionLogFormatter`) és/vagy adatbázisba (`Notify` + `NotifyCall` – egy hibához több előfordulás) menti, ill. emailt küld. Egyedi figyelmeztető levél: `ExceptionNotifier::sendWarningMail($message, $body, $subject = null)`.

### Akciók

A `Traits\LoggableTrait` (vagy a kész `Controller\LoggableController` ős) védett metódusokat ad, amik `LogActionEvent`-eket dobnak; az `ActionLogSubscriber` ezekből ír `LogAction` rekordot (időpont, befejezés ideje, user, eredeti user impersonate esetén, IP, user agent, URL, POST, siker/hiba).

```php
class OrderController extends LoggableController
{
    public function save(Order $order, Request $request)
    {
        $this->logStart('order.save', ['%id%' => $order->getId()], $order);
        // ... munka ...
        $this->logUpdate(['%step%' => 'invoice']);
        $this->logDone();
    }
}
```

| Metódus | Hatás |
|---|---|
| `logStart($type, $params, $object, $priority, $extra)` | akció megnyitása (`hgabka_logger.log_start`) |
| `logUpdate($params, $object, $priority, $extra)` | megnyitott akció frissítése |
| `logDone()` | akció lezárása |
| `logError($object, $extra)` / `logFormErrors($type, $params, $form, $object)` | hiba / űrlaphibák rögzítése |
| `actionLog(...)` | egyszeri start+done |

A `$type` és a paraméterek a `logger` fordítási doménen keresztül kerülnek fordításra (`Resources/translations/logger.{hu,en}.yaml`). Ha a trait-et nem kontrollerben használod, a `EventDispatcherInterface`-t a `#[Required]` setter injektálja.

### Mezőváltozások

```php
use Hgabka\LoggerBundle\Helper\LoggableEntityInterface;

class Product implements LoggableEntityInterface
{
    public function getLogFields(): ?array { return ['name', 'price']; }   // [] = minden mező, null = semmi
    public function getNotLogFields(): ?array { return ['internalNote']; }
}
```

A `ColumnLogListener` a Doctrine `onFlush` eseményén fut, és mezőnként egy `LogColumn` sort készít (tábla, osztály, `foreign_id`, régi/új érték, módosítás típusa, user, URL, controller, kliens adatok). Törléskor/beszúráskor az entitás teljes adata JSON-ként a `data` mezőbe kerül. Ha `getLogFields()` üres tömb, minden mezőt naplóz, kivéve a `createdAt`/`updatedAt`-et.

## Entitások

| Entitás | Tábla | Tartalom |
|---|---|---|
| `LogAction` | `hg_logger_log_action` | akció-napló |
| `LogColumn` | `hg_logger_log_column` | mezőváltozás-napló |
| `Notify` / `NotifyCall` | `hg_logger_notify` / `_call` | kivételek és előfordulásaik |
