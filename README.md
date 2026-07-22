# The Drop Vinyls - E-commerce de Vinilos

The Drop Vinyls es una plataforma web completa diseñada para melómanos que buscan la experiencia del ritual del vinilo en la era digital. El sistema permite la gestión de inventario, navegación dinámica de productos, carrito de compras seguro y facturación con generación de registros PDF.

---

## 🛠️ Requisitos Previos

Para poder instalar y ejecutar este proyecto de forma local, asegúrate de tener instalado en tu equipo:
- **Git** (Para clonar el repositorio)
- **XAMPP / WAMP / MAMP** (Entorno con soporte para PHP 8.0+ y MySQL/MariaDB)
- **PHPUnit** (Opcional, solo si deseas ejecutar las pruebas unitarias localmente)

---

## 📥 Instrucciones de Instalación

Sigue estos pasos exactos para levantar el sistema desde cero:

1. **Clona el repositorio** en la carpeta pública de tu servidor local (ej. `C:\xampp\htdocs\`):
   ```bash
   git clone https://github.com/Hernan416/ecommerce-laury-hernan.git
   cd ecommerce-laury-hernan/src
   ```

2. **Inicia los servicios** de Apache y MySQL desde el panel de control de XAMPP.

3. **Configura la Base de Datos**:
   - Abre tu navegador y dirígete a `http://localhost/phpmyadmin`.
   - Crea una nueva base de datos llamada **`the_drop_vinyls`**.
   - Importa el archivo `the_drop_vinyls.sql` que se encuentra en la raíz de la carpeta `src/`. Esto creará todas las tablas e insertará datos de prueba.

4. **Accede al sistema**:
   Abre tu navegador y visita: `http://localhost/ecommerce-laury-hernan/src/`

---

## 📚 Documentación de la API

El sistema expone endpoints para comunicación asíncrona devolviendo formato JSON. A continuación se encuentra la tabla de los endpoints principales implementados en la arquitectura:

| Verbo HTTP | Ruta | Descripción | Request (Cuerpo) | Response Esperado (JSON) |
| :--- | :--- | :--- | :--- | :--- |
| `POST` | `/api/v1/pedidos.php` | Procesa de forma simulada (Mock) una transacción de pago externa. | `{"monto": 150.00, "id_usuario": 3}` | `{"status": "success", "message": "Pago procesado exitosamente.", "transaction_id": "txn_...", "monto_procesado": 150.00}` |
| `GET` | `/Usuarios/index.php?ajax=1&buscar={query}` | Endpoint dinámico asíncrono para filtrar el catálogo sin recargar la página. | N/A (Se envía por query string) | Fragmento HTML con las tarjetas de los productos que coinciden con la búsqueda. |

---

## 💾 Ejecución de Pruebas Unitarias

El proyecto cuenta con validaciones y pruebas para la lógica de negocios utilizando el framework **PHPUnit**, enfocadas en los patrones de diseño y cálculos de Checkout.

Para ejecutar las pruebas en tu entorno local (asumiendo que tienes el binario `phpunit.phar` en la raíz del proyecto o PHPUnit instalado globalmente):

1. Abre tu terminal de comandos en la carpeta raíz del proyecto.
2. Ejecuta el siguiente comando para correr todas las pruebas:
   ```bash
   php phpunit.phar tests/CheckoutTest.php
   ```
   *Alternativamente, si usas la ruta binaria instalada globalmente:*
   ```bash
   phpunit tests/CheckoutTest.php
   ```

3. Verás la salida de validación verde confirmando que la herencia y las estrategias de descuento (Patrón Strategy) funcionan correctamente de forma matemática.
