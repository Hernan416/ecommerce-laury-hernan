# The Drop Vinyls - E-commerce de Vinilos

The Drop Vinyls es una plataforma web completa diseñada para melómanos que buscan la experiencia del ritual del vinilo en la era digital. El sistema permite la gestión de inventario, navegación dinámica de productos, carrito de compras seguro y facturación con generación de registros PDF.

---

## 🛠️ Requisitos Previos

Para poder instalar y ejecutar este proyecto de forma local, asegúrate de tener instalado en tu equipo:
- **Git** (Para clonar el repositorio)
- **Docker y Docker Compose** (Requerido para levantar el entorno aislado)

---

## 📥 Instrucciones de Instalación

Sigue estos pasos exactos para levantar el sistema desde cero:

1. **Clona el repositorio** en tu máquina:
   ```bash
   git clone https://github.com/Hernan416/ecommerce-laury-hernan.git
   cd ecommerce-laury-hernan
   ```

2. **Levanta el sistema con Docker**:
   Ejecuta el siguiente comando en la raíz del proyecto para construir la imagen y levantar los contenedores de la aplicación y la base de datos (la cual se importará automáticamente):
   ```bash
   docker compose up --build
   ```

3. **Accede al sistema**:
   Abre tu navegador y visita: `http://localhost:8000`

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

Para ejecutar las pruebas en tu entorno local:

1. Abre una **nueva** terminal en la carpeta raíz del proyecto (sin cerrar la terminal donde Docker está corriendo).
2. Entra al contenedor del servidor web ejecutando:
   ```bash
   docker compose exec app bash
   ```
3. Estando dentro del contenedor, ejecuta las pruebas con el siguiente comando:
   ```bash
   php phpunit-10.phar tests/CheckoutTest.php
   ```

4. Verás la salida de validación verde confirmando que la herencia y las estrategias de descuento (Patrón Strategy) funcionan correctamente de forma matemática.
