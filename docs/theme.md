# Theme

This platform use [Bootstrap](https://getbootstrap.com/). For customize boostrap,
override `./assets/styles_custom-variables.css`.

## Example

The base value for the color $primary is: 

```
$blue-500: #0D6EFD; 
$dark: $blue-500;
```

You can see this in the navbar. For the default theme, we have choice the background-color dark with the bootstrap class: `navbar-dark bg-dark`

```
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    ...
</nav>
```

![Basic $primary color](images/base-color.png)


### Customize!

For add your color theme, go to `/assets/styles_custom-variables.css`.

First, declaring your color with a name and value: `$salmon: #FFA07A;`

Next, let's assign this value to the variable `$primary` of boostrap : `$primary: $salmon;`

[The other bootstrap variable name](https://getbootstrap.com/docs/5.0/customize/color/)

Then, change the default navbar class with our new color. Attention, for the example we have choice to assign our color to the `$primary` bootstrap color. For see the new color, use `navbar-primary bg-primary` 

```
<nav class="navbar navbar-expand-lg navbar-primary bg-primary">
    ...
</nav>
```

TADA ! We have customized the color !!

![Updated color](images/updated-color.png)

For more information, see the [Bootstrap customize doc](https://getbootstrap.com/docs/5.3/customize/overview/).
