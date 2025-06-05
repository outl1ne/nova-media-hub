# Laravel Nova 5 Upgrade - Changelog

## Aggiornamenti delle dipendenze

### Package.json - devDependencies
- **@inertiajs/inertia** `^0.11.1` → **@inertiajs/vue3** `^2.0.11` (aggiornato per Inertia.js 2)
- **@vue/babel-plugin-jsx** `^1.1.1` → `^1.1.5`
- **axios** `^0.27.2` → `^1.7.2`
- **postcss** `^8.4.18` → `^8.4.31`
- **postcss-import** `^15.0.0` → `^15.1.0`
- **postcss-rtlcss** `^3.6.3` → `^4.0.6`
- **prettier** `^2.7.1` → `^3.0.3`
- **sass** `^1.55.0` → `^1.77.8`
- **sass-loader** `^13.1.0` → `^13.3.2`
- **tailwindcss** `^3.2.1` → `^3.4.6`
- **terser-webpack-plugin** `^5.3.6` → `^5.3.9`
- **vue-loader** `^17.0.1` → `^17.4.2`

### Package.json - dependencies
- **vue** `^3.2.41` → `^3.4.31`
- **vue-simple-context-menu** `^4.0.4` → `^4.1.0`

### Rimosse
- **vue-template-compiler** (non più necessario con Vue 3)
- **vuex** (non utilizzato nel progetto)

## Configurazioni aggiornate

### webpack.mix.js
- Aggiunto `laravel-nova` agli externals per compatibilità con Nova 5
- Aggiunto alias per risolvere i path

### postcss.config.js (nuovo file)
- Creato file di configurazione PostCSS locale per evitare conflitti
- Configurato con i plugin necessari: postcss-import, tailwindcss, postcss-rtlcss

## Compatibilità

### Laravel Nova 5
- Il pacchetto è ora compatibile con Laravel Nova 5.x
- Mantiene la compatibilità con Laravel Nova 4.x (come specificato nel composer.json)

### Inertia.js 2
- Aggiornato per utilizzare @inertiajs/vue3 v2.x
- I componenti Head e Link sono forniti globalmente da Nova

### Vue 3
- Aggiornato a Vue 3.4.x per migliori performance e compatibilità

## Note per gli sviluppatori

1. **Build riuscita**: La compilazione funziona correttamente con tutte le nuove dipendenze
2. **Compatibilità backwards**: Il pacchetto mantiene la compatibilità con versioni precedenti
3. **Dipendenze esterne**: Laravel Nova e Vue sono configurati come externals nel webpack
4. **PostCSS**: Configurazione locale per evitare conflitti con altri progetti nel workspace

## Prossimi passi raccomandati

1. Testare il pacchetto in un ambiente Nova 5
2. Verificare che tutte le funzionalità di upload e gestione media funzionino correttamente
3. Controllare la compatibilità con eventuali altri pacchetti Nova presenti nel progetto
4. Aggiornare la documentazione se necessario
