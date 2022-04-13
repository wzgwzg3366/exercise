<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/css/bootstrap.min.css" integrity="sha384-p1KAotb3W9ndluCsqePPYnjRm3c6abdnIjo0tQwYUv83VsbsYd43RuofnFAaDo0E" crossorigin="anonymous">
	<link rel="stylesheet" href="https://necolas.github.io/normalize.css/8.0.1/normalize.css" integrity="sha384-p1KAotb3W9ndluCsqePPYnjRm3c6abdnIjo0tQwYUv83VsbsYd43RuofnFAaDo0E" crossorigin="anonymous">

        <title>设置放学时间</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@200;600&display=swap" rel="stylesheet">

        <!-- Styles -->
        <style>
            html, body {
                background-color: #fff;
                color: #636b6f;
                font-family: 'Nunito', sans-serif;
                font-weight: 200;
                height: 100vh;
                margin: 0;
            }
	    .form-group {width:100%}
        </style>
    </head>
    <body
        <div class="container">
            <div class="content">
<form role="form">
  <div class="form-group">
    <label for="name">周一放学时间:</label>
    <input type="text" class="form-control" value="16:30" id="name" placeholder="请输入时间">
  </div>
  <div class="form-group">
    <label for="name">周二放学时间:</label>
    <input type="text" class="form-control" value="16:30" id="name" placeholder="请输入时间">
  </div>
  <div class="form-group">
    <label for="name">周三放学时间:</label>
    <input type="text" class="form-control" value="16:30" id="name" placeholder="请输入时间">
  </div>
  <div class="form-group">
    <label for="name">周四放学时间:</label>
    <input type="text" class="form-control" value="16:30" id="name" placeholder="请输入时间">
  </div>
  <div class="form-group">
    <label for="name">周五放学时间:</label>
    <input type="text" class="form-control" value="13:55" id="name" placeholder="请输入时间">
  </div>
  <div class="form-group">
    <input type="submit" class="form-control" style="width:100%;height:30px" id="name" value="保存">
  </div>
</form>
            </div>
        </div>
    </body>
</html>
