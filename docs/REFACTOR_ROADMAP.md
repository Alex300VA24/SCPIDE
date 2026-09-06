# Roadmap de Refactorings Sugeridos

Mejoras de arquitectura NO CRÍTICAS para repo público, pero recomendadas a mediano plazo.

## 1. Modularizar CSS (Prioridad: Media)

**Problema:** `resources/css/app.css` es 2000+ líneas, minificado, difícil mantener.

**Solución:**
```
resources/css/
├── components/
│   ├── base.css      (variables, resets)
│   ├── forms.css     (.field, .button-*, .input)
│   ├── tables.css    (.data-table, .toolbar)
│   ├── modals.css    (.modal-backdrop, .modal-panel)
│   └── legacy.css    (.modulo-legacy)
├── dashboard/
│   ├── sidebar.css   (.sideBar, .sidebar-*, .sidebar-nav)
│   ├── layout.css    (.main-content, .dashboard-container)
│   └── animations.css (.glass, .legacy-alert-in, etc.)
└── app.css           (@import de las demás + @tailwind)
```

**Ventaja:** cada cambio CSS localizado, merge conflicts reducidos.

**Config:** Tailwind `content.paths` o Vite postcss imports.

**Esfuerzo:** 2-4 horas.

---

## 2. Unificar PDF Export Pattern (Prioridad: Media)

**Problema:** Cada `Consulta*.php` duplica `private function buildPdfToken()`.

**Solución:** Crear trait base `CachesPdfConsultation` con método genérico:

```php
trait CachesPdfConsultation {
    protected function buildConsultaPdf(
        string $title,
        string $subtitle,
        string $service,
        string $logo,
        array $metaFields,      // DNI, RUC, etc.
        array $sections,        // heading, rows/columns
        ?string $filename = null
    ): string {
        // cacheConsultaPdf() centralizado
        return $this->cacheConsultaPdf([
            'entity' => ['name' => $service, 'logo' => $logo],
            'title' => $title,
            'subtitle' => $subtitle,
            'meta' => $metaFields,
            'filename' => $filename ?? strtolower($service),
            'sections' => $sections,
        ]);
    }
}
```

**Uso en ConsultaMtc:**
```php
$this->buildConsultaPdf(
    'Récord Conductor',
    'MTC licencias/papeletas/sanciones',
    'MTC',
    'mtc-logo.png',
    [$this->tipoDocLabel => $this->numeroDocumento],
    $sections  // construida dinámicamente
);
```

**Beneficio:** una línea en lugar de 40 líneas duplicadas.

**Esfuerzo:** 1-2 horas.

---

## 3. Extraer Demo Data Factory (Prioridad: Baja)

**Problema:** `app/Services/PideDemo/*Service` tiene data hardcodeada repetida.

**Solución:** `app/Services/PideDemo/Support/DemoDataFactory.php` existe pero podría:

```php
class DemoDataFactory {
    public static function personaConadis(string $nombre = 'Demo'): array {
        return [
            'nombre' => $nombre,
            'apellidoPaterno' => 'Demonio',
            'apellidoMaterno' => 'Ficticio',
            'estado' => 1,
            'gravedad' => 2,
            'fallecido' => false,
        ];
    }

    public static function licenciaMtc(string $categoria = 'A2M'): array {
        return [
            'numLicencia' => '12345678',
            'categoria' => $categoria,
            'fecExp' => date('Y-m-d', strtotime('-2 years')),
            'fecRev' => date('Y-m-d', strtotime('+1 year')),
            'estado' => 'VIGENTE',
        ];
    }
}
```

**Uso:** `return ['success' => true, 'data' => DemoDataFactory::personaConadis()];`

**Beneficio:** DRY, datos consistentes, fácil update.

**Esfuerzo:** 1-2 horas.

---

## 4. Trait `BuildsConsultaPdf` → Interface Contract (Prioridad: Baja)

**Problema:** trait BuildsConsultaPdf no es contrato explícito.

**Solución:**
```php
interface ConsultaPdfBuilder {
    public function cacheConsultaPdf(array $payload): string;
}

trait BuildsConsultaPdf implements ConsultaPdfBuilder { ... }
```

**Beneficio:** claridad de responsabilidad, testeable con mock.

**Esfuerzo:** 30 min.

---

## 5. Centralizar Validación de Documentos (Prioridad: Media)

**Problema:** `regex:/^\d{8}$/` repetido en ConsultaDni, ConsultaConadis, etc.

**Solución:** Validator custom:
```php
// app/Http/Requests/DocumentValidator.php
class DocumentValidator {
    const DNI = '/^\d{8}$/';
    const RUC = '/^\d{11}$/';
    const PASAPORTE = '/^[A-Z]{2}\d{7}$/';
}
```

**Uso:** `'numeroDocumento' => ['required', 'regex:'.DocumentValidator::DNI]`

**Esfuerzo:** 30 min.

---

## 6. Event/Listener para PDF Token Cleanup (Prioridad: Baja)

**Problema:** PDF tokens expiran en Cache pero no se limpian explícitamente.

**Solución:** Listener en `RequestHandled` (como existe en AppServiceProvider):

```php
Cache::forget("consulta_pdf:{$token}");  // manual cleanup
```

O confiar en TTL de Laravel Cache (OK para production).

**Esfuerzo:** 1 hora (opcional).

---

## 7. Modularizar BaseConsultation (Prioridad: Baja)

**Problema:** BaseConsultation contiene múltiples responsabilidades.

**Solución:** Extraer concerns:
- `HandlesPideSearch` — search(), resetSearch()
- `HandlesPideFallback` — fictitiousFallback*, useFictitiousData()
- `HandlesPipeCredentials` — onPideCredentialSaved(), credential checks

Usar múltiples traits.

**Beneficio:** SRP, más legible.

**Esfuerzo:** 2-3 horas.

---

## 8. Add Tests (Prioridad: Alta post-lanzamiento)

**Coverage actual:** TBD (check with `php artisan test --coverage`)

**Recomendado:**
- Unit: Services/Pide (mocks de HttpClient)
- Feature: Livewire components (demo mode)
- Integration: full stack con Demo services

**Esfuerzo:** 4-8 horas por suite.

---

## 9. TypeScript / Vue Setup (Prioridad: Muy baja)

**Si:** Livewire + Alpine aún insuficiente para nuevas features.

**Entonces:** setup Inertia + Vue o Livewire v3 reactive model.

**Esfuerzo:** 8+ horas.

---

## 10. Database Refactor (Prioridad: Muy baja)

Cuando sistema stabiliza, considerar:
- Normalización relaciones usuario/módulos
- Índices performance queries
- Query builder → Eloquent resource classes

**Esfuerzo:** variable según cambios.

---

## Priorización por Impacto

| Orden | Refactor | Impacto | Esfuerzo |
|-------|----------|---------|----------|
| 1     | CSS modularización | Mantenibilidad | 2-4h |
| 2     | PDF export unificar | DRY, reducir bugs | 1-2h |
| 3     | Demo factory centralizar | Consistencia | 1-2h |
| 4     | Tests suite | Confiabilidad | 4-8h |
| 5     | Validación centralizar | DRY | 30m |
| 6     | BuildsConsultaPdf → Interface | Claridad | 30m |
| 7     | BaseConsultation concerns split | SRP | 2-3h |
| 8     | Event listener cleanup | Higiene | 1h |
| 9     | TypeScript/Vue | Feature velocity | 8+h |
| 10    | Database refactor | Escalabilidad | Variable |

---

## Próximos Pasos

1. **Inmediato (pre-public):** Security audit checklist (docs/PUBLISH_CHECKLIST.md)
2. **Week 1 post-launch:** Tests suite (#8)
3. **Month 1:** CSS modularización (#1) + PDF unificación (#2)
4. **Month 2+:** Remaining based on dev feedback

---

## Notas

- No bloquean repositorio público
- Refactors no cambian comportamiento usuario (no-breaking)
- Usar feature branches, PRs con review antes merge
- Cada refactor = PR separate
