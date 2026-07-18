<!--
Template para PRs de E1 Connect Inventory.
Este plugin se distribuye por GitHub (self-update con GABECODE_GH_TOKEN):
el updater sigue la cadena Release → tag → rama main, así que un merge a main
con la versión bumpeada YA entrega la actualización a los sitios conectados.
-->

## 🎫 Ticket de Asana

<!-- Pega el enlace del ticket PRIMERO — es la fuente de contexto del PR. Si no hay ticket, escribe "N/A" y explica el origen del cambio en el resumen. -->

**Enlace:**

## 📝 Resumen

<!-- Qué cambia y por qué, en 2-4 líneas. Contexto suficiente para alguien que no siguió el desarrollo ni leyó el ticket. -->

## 🏷️ Tipo de cambio

- [ ] ✨ Feature nueva
- [ ] 🐛 Bugfix
- [ ] ♻️ Refactor (sin cambio de comportamiento)
- [ ] 🎨 Admin UI / estilos
- [ ] 🗃️ Modelo de datos (CPT `vehicle`, taxonomías, meta `_e1ci_*`)
- [ ] 🖥️ Comando WP-CLI (`wp e1ci …`)
- [ ] 🔌 API / integración externa (inventory-search REST, VIN decoder, descripciones LLM…)
- [ ] 🖼️ Frontend (templates, shortcodes, assets)
- [ ] 🔄 Sistema de updates / release
- [ ] ⚙️ Configuración / tooling

## ✅ Pruebas

<!--
Sé específico: un QA (o tú en 3 meses) debe poder reproducir esto sin preguntar.
Cada escenario lleva pasos concretos + resultado esperado. Agrega los que necesites.
-->

**Entorno de pruebas:**

- Sitio: <!-- ej. every1drives-gc en LocalWP -->
- Requisitos previos: <!-- ej. GABECODE_GH_TOKEN definida en wp-config, plugin activo, `wp e1ci migrate --commit` ejecutado… o "Ninguno" -->

**Escenario 1 — <!-- nombre corto, ej. "Guardar precio desde el metabox" -->**

1. <!-- paso concreto: URL, pantalla, comando -->
2. <!-- … -->

- **Resultado esperado:** <!-- qué debe verse / devolver / guardarse -->
- **Resultado obtenido:** <!-- lo que pasó realmente al probarlo -->

**Escenario 2 — <!-- caso borde o regresión: datos vacíos, sin token, rol sin permisos… -->**

1. <!-- … -->

- **Resultado esperado:**
- **Resultado obtenido:**

**Evidencia:**

- [ ] Probado en **local** (LocalWP)
- [ ] Sin errores/warnings PHP nuevos (`debug.log` limpio)
- [ ] `php -l` limpio en los archivos tocados
- [ ] Comandos WP-CLI probados en dry-run primero (`wp e1ci migrate` sin `--commit`) — si aplica
- [ ] Endpoint `e1ci/v1/inventory-search` probado (respuesta y auth) — si el PR lo toca
- [ ] Verificado que NO rompe la convivencia con `gabecode-plus` / theme activo — si aplica

## 📸 Screenshots / video

<!-- Antes/después para cambios de admin UI o frontend. Borra la sección si no aplica. -->

## 🚀 Release (post-merge)

<!--
El updater lee la rama main: al mergear con la versión bumpeada, los sitios
con GABECODE_GH_TOKEN reciben la actualización en el siguiente check de updates.
-->

- [ ] Versión bumpeada en **ambos** lugares: header `Version:` y `E1CI_PLUGIN_VERSION` en `e1connect-inventory.php` (+ `Stable tag` en `readme.txt`)
- [ ] Changelog actualizado en `readme.txt`
- [ ] Acciones post-update en los sitios: <!-- ej. re-guardar settings, flush rewrites (visitar Ajustes → Enlaces permanentes)… o "Ninguna" -->
- [ ] N/A — este PR no requiere release (docs, CI, tooling)

## 🔍 Checklist del autor

- [ ] Funciones nuevas con prefijo `e1ci_` (clases `E1CI_`, meta `_e1ci_*`, filtros `e1ci/*`)
- [ ] Docblocks en inglés con `@author Gabriel Coronado` y `@since <versión o branch>`
- [ ] Callbacks en `includes/` y hooks registrados en `e1connect-inventory.php` (salvo módulos autocontenidos)
- [ ] Clases base nombradas `abstract-*` / `interface-*` / `trait-*` para respetar el orden de carga del loader
- [ ] Directorios nuevos con su `index.php` guard
- [ ] Sin `console.log` / `var_dump` / código comentado de debug
- [ ] Sin archivos locales en el diff: `CLAUDE.md`, `.claude/`, credenciales
- [ ] Cambios de migración respetan el contrato aditivo/reversible de `meta-key-map.php`
