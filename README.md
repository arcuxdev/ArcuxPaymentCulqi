# Arcux Payment Culqi
- Usando un shortcode generará un formulario de pagos con la pasarela de pagos Culqi.
- Orientado para la venta de cursos.
- El Plugin generará shortcode por cada curso generado y este mostrará un formulario de pago.
- El pago es en dolares (por el momento.)

## Funciones
  - Generar pagos con la pasarela Culqi.
  - Se uso WP_List_Table para mostrar la información en una tabla dinamica en el administrador de WordPress.
  - Tiene la opcion de notificar por slack y por email cuando se realice un pago. (opcional)
  - Para el usuario final se crea un shorcode por pago estos los pueden añadir en una pagina, articulo o widgets.

## Uso
- El Shortcode recopila información y la envia a el panel de administración
```sh
ShorCode Ejemplo:
[payment-culqi course="CourseUno" submit="Pagar" plan="full" image="" redirect=""]
```

* course - Id identificativo del pago realizado
* submit - Label del boton de enviar formulario.
* plan - existen 3 tipos de planes (year, full, free)
* imagen - imagen descriptiva de la compra.
* redirect - url donde redirigira una vez realizado el pago.

## Instalación
```sh
patch: wp-content/plugins/
git clone git@github.com:arcuxdev/ArcuxPaymentCulqi.git payment-culqi
cd payment-culqi
```

## Activar
```sh
Go url: /wp-admin/plugins.php
Activate "Arcux Payment Culqi"
```

## Desarrollo
 - Luis Gago Casas
 - Karina Ramos

License
----
GPL2
* License URI: https://www.gnu.org/licenses/gpl-2.0.html