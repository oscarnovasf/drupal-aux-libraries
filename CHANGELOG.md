# Histórico de cambios
>Todos los cambios notables de este proyecto se documentarán en este archivo.

## [v0.1.3] - 2022-07-06
>Revisión
### Añadidos
* Nuevas funciones de validación de datos.
* Manejo de errores en ResponseFunctions.

### Corrección de errores
* Eliminación de funciones obsoletas de php.

---
## [v0.1.2] - 2022-06-17
>Revisión
### Añadidos
* Librería ***DrupalLog***.

### Modificados
* Refactor de la función `getResponse` en ***ResponseFunctions***.

---
## [v0.1.1] - 2022-04-11
>Revisión
### Añadidos
* Ahora se permite el uso de contenedores dentro de los campos múltiples en la
  librería ***MultiValue.php***.

### Corrección de errores
* Problema con los campos `entity_autocomplete` en la librería
  ***MultiValue.php***.

---
## [v0.1.0] - 2022-04-06
>Revisión
### Añadidos
* Nueva función en ***StringFunctions.php*** para eliminar BOM.
* Nueva librería para crear elementos multiples en formularios
  ***MultiValue.php***.

### Corrección de errores
* Error en getResponse en ***ResponseFunctions.php*** cuando no se indicaba la
  key a devolver.

---
## [v0.0.9] - 2022-02-17
>Revisión
### Añadidos
* Nueva función en ***FileFunctions.php*** para obtener un array a partir de
  un archivo en formato XML.
* Nuevas funciones en ***StringFunctions.php*** para eliminar espacios entre
  palabras y eliminar saltos de línea.

---
## [v0.0.8] - 2021-10-27
>Revisión
### Añadidos
* Nueva función en ***ResponseFunctions.php*** para obtener el resultado sin
  caché.

---
## [v0.0.7] - 2021-06-25
>Revisión
### Añadidos
* Nueva librería ***CalendarLinkFunctions.php*** para gestión de creación de
  eventos en diferentes calendarios (Google, Yahoo, Outlook, ICS...).

### Corrección de errores
* Error en la cláusula uses de ***DateTimeFunctions.php***.

### Modificados
* Mejora de los comentarios en los archivos de las librerías.

---
## [v0.0.6] - 2021-05-07
>Revisión
### Añadidos
* Se añade función a FileFunctions para listar los archivos de un directorio.
* Se añade función a FileFunctions para mover un archivo.

### Corrección de errores
* Error con uno de los parámetros en una función de FileFunctions.php

---
## [v0.0.5] - 2021-04-21
>Revisión
### Añadidos
* Nueva librería ***MarkdownParser.php***.

---
## [v0.0.4] - 2021-03-15
>Revisión
### Añadidos
* Nuevos parámetros para la librería ***Mailing.php***.
* Nueva función dentro de ***StringFunctions.php*** para limpiar la etiqueta
  style de una cadena de texto.

### Modificados
* Mejora en los comentarios de las librerías para adaptarlos al uso de
  PHPDox.

---
## [v0.0.3] - 2021-02-09
>Revisión
### Añadidos
* Nueva librería ***Mailing.php*** para creación de mails.

### Corrección de errores
* Reparación de método ***cleanDirectory*** en ***FileFunctions.php***.

---
## [v0.0.2] - 2021-01-30
>Revisión
### Añadidos
* Nuevas funciones en ***StringFunctions.php***.
* Nuevas funciones en ***FileFunctions.php***.
* Nueva librería ***DateTimeFunctions.php***.

---
## [v0.0.1] - 2021-01-08
> Primera versión.
