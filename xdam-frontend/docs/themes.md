<ol class="breadcrumb">
    <li>Themes</li>
</ol>

In XDAM a theme is a simple CSS file with adjustments to visual styles. Let's look at the base XDAM theme:

```css
/* Base theme */
@import url('https://fonts.googleapis.com/css?family=Lato');
@import url('../style.css');

.dam-main{
    font-family: 'Lato', sans-serif;
    color: #666;
}
.shadow-panel{
    box-shadow: 0 1px 1px 0 rgba(0,0,0,.12);
}
.dam-main-back{
    background-color: #EDEFF2;
}
.dam-item{
    font-size: 13px;
    background: #FFF;
    border-top: 2px solid rgb(177, 177, 177);
    transition: all .25s;
    box-shadow: 0 1px 1px 0 rgba(0,0,0,.12);
}
.dam-type{
    font-size: 10px;
    font-weight: 700;
    color: #666;
}
.dam-btn-base{  
    background: linear-gradient(180deg,#00a397 0,#009E92 100%);
    color: white; 
    font-size: 10px;
    box-shadow: none;  
    border-left: 1px dotted #E0E4E6;
    font-size: 12px;
}
.dam-btn-base:hover {
    background: #00a397;
    background: linear-gradient(180deg,#00a89b 0,#00a397 100%); 
}
.dam-search-bar{
    background-color: white;
}
.dam-btn-base.group {
    border:1px solid #00a89b;
}
.dam-btn-base.red{
    background: linear-gradient(180deg,#c54444 0,#E35D5D 100%);
}
.dam-form input[type="text"], select{
    font-family: Lato,sans-serif;
    font-size: 13px;
    color: #666;
    background-color: #f9fafb;
    border: 1px solid #DDD;
    transition: all .25s;
}
.dam-form input[type="text"]:focus{
    outline: 0;
    background-color: #FFF;
    border-color: rgba(0,163,151,.5);
    box-shadow: 0 0 10px 0 rgba(0,163,151,.05);
}
.dam-form input[type="text"].ng-invalid.ng-dirty{
    border-color: rgba(250,80,80,.5);
    box-shadow: 0 0 10px 0 rgba(250,80,80,.5);
}

```

As we can see themes support any modification to any component class, although they are more oriented to colors, fonts, borders and visual styles than structural styles.

## Creating a theme

Creating a new theme is very simple, and can be done in three simple steps:

### 1. Create your file

Create a new CSS file in **/dam/themes/** with any name you want, and make sure you import the main style of the application, like this:

```css
@import url('../style.css');
```

### 2. Add your content

Now you are free to add any styles you want your theme to feature. Main specific classes for visual styling are:

* **dam-main**: The main application container.
* **dam-main-back**: The DAM background.
* **dam-item**: Class used for every resource item in the grid.
* **dam-btn-base**: Class used across most buttons in the app.

### 2. Import your theme

Import your theme in the **styles.css** of your main application. If you are using the XDAM as an application (Not as a package) you can use this route:

```css
@import url('lib/dam/themes/light.css');
```

In case you are using it as a package you'll have to point to your respective **node_modules** path. <br/> <br/>