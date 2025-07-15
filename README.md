# Project News System

Este proyecto utiliza varias dependencias gestionadas con **npm** para el desarrollo y construcción de estilos y utilidades.

## Instalación de dependencias

1. **Instala todas las dependencias**  
   Ejecuta en la raíz del proyecto:

   ```sh
   npm install
   ```

   Esto instalará todas las dependencias listadas en `package.json`.

## Scripts disponibles

- **Compilar Tailwind CSS en modo desarrollo (con watch):**
  ```sh
  npx @tailwindcss/cli -i ./src/input.css -o ./src/output.css --watch
  ```
  Genera el archivo `src/input.css` y `src/output.css` observa cambios en tiempo real.

- **Compilar Tailwind CSS para producción (minificado):**
  ```sh
  npm run build-css-prod
  ```
  Genera el archivo `assets/css/style.css` optimizado para producción.

- **Ejecutar pruebas (placeholder):**
  ```sh
  npm test
  ```

## Dependencias principales

- [tailwindcss](https://tailwindcss.com/)
- [@tailwindcss/cli](https://github.com/tailwindlabs/tailwindcss-cli)
- [postcss](https://postcss.org/)
- [autoprefixer](https://github.com/postcss/autoprefixer)

## Notas

- Si agregas nuevas dependencias, recuerda ejecutar `npm install`.
- Para eliminar una dependencia, usa `npm uninstall <nombre-paquete>`.
- Para agregar una nueva dependencia de desarrollo:
  ```sh
  npm install -D <nombre-paquete>
  ```

---

Consulta la [documentación oficial de npm](https://docs.npmjs.com/) para más información sobre la gestión