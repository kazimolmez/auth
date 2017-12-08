<?php



require_once __DIR__.'/bootstrap.php';



if(post('login') && $email = post('email') && $password = post('password'))
{

    if(!isAuthentication('user'))
    {
      $attempt = auth('user')->attempt(['email'=> $email,'password' => $password ],post('remember'));

      if ($attempt)
      {
        $info = "Logged in ".auth()->name;
      }
      else
      {
        $info = auth()->getMessage();
      }
    }
    else
    {
      echo "You are already logged in";
    }
}


?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>Auth Login</title>
    <style media="screen">
      *{
        margin: 0;
        padding: 0;
        box-sizing: border-box;
      }
      .container{
        padding: 15px;
        margin: 0;
        align-items: center;
      }
      .rem p{
        display:inline-block;
        vertical-align: middle;
      }
      .rem input{
        vertical-align: middle;
      }
      form{
        max-width: 400px;
        height: auto;
        margin-left: auto;
        margin-right: auto;
        margin-top: 100px;
        border:1px solid #ddd;
        padding: 10px;
        border-radius: 4px;
        box-shadow: 4px 3px rgb(56, 49, 61);
        background-color:rgb(61, 67, 69);
        color:white;
      }


      form table{
        margin-left: auto;
        margin-right: auto;
      }

      form  input:not([type="checkbox"]){
        width: 300px;
        border-radius: 0px;
        border:1px solid #ddd;
        padding: 5px;
        display: block;
        height: 30px;
        font-size: 14px;
      }


      form input[type="submit"]{
        border-radius: 0px;
        cursor: pointer;
        background-color: #2d80c4;
        color:white;
        display: block;
      }

      .info{
        width: 300px;
        max-height: 40px;
        background-color: #66359d;
        color:white;
        text-align: center;
        padding: 5px;
        border: 1px solid #ddd;
        position: relative;
      }

      .hidden{
        display: none;
      }
      td{
        padding: 5px;
      }

      .info > .close {
        position:absolute;
        top:-1.5px;
        right:-7.5px;
        cursor: pointer;
        display: block;
        font-weight: bold;
        color: red !important;
      }

      .info > .close span{
        animation: pop 1s infinite;
      }

      @keyframes pop {
        50%{
          width: 16px;
          height: 3px;
        }
        100%{
          width: 14px;
          height: 2px;
        }
      }

      @-webkit-keyframes pop{
        50%{
          width: 16px;
          height: 3px;
        }
        100%{
          width: 14px;
          height: 2px;
        }
      }

      .info > .close .first{
        display: block;
        transform: rotate(-45deg);
        width: 14px;
        height: 2px;
        color: red;
        background-color: rgb(249, 60, 60);
      }
      .info > .close .last{
        display: block;
        transform: rotate(-135deg);
        width: 14px;
        height: 2px;
        position: absolute;
        top:0px;
        background-color: red;
      }
    </style>
  </head>
  <body>
    <div class="container">
      <form action="<?php echo $_SERVER['PHP_SELF']  ?>" method="post">
        <table>
          <tr class="<?php echo isset($info) ? "" : "hidden" ?>" >
            <td class="info">
              <span class="close" onclick="hideParent(this)">
                <span class="first"></span>
                <span class="last"></span>
              </span>
              <?php echo $info ?? "" ?>
            </td>
          </tr>
          <tr></tr>
          <tr></tr>
          <tr>
            <td>Email</td>
          </tr>
          <tr>
            <td><input type="text" name="email" placeholder="email" required></td>
          </tr>
          <tr>
            <td>Password</td>
          </tr>
          <tr>
            <td><input type="password" name="password" placeholder="password" required></td>
          </tr>
          <tr>
            <td class="rem"><p>Remember Me </p> <input type="checkbox" name="remember"></td>
          </tr>
          <tr><td><hr></td></tr>
          <tr>
            <td><input type="submit" name="login" value="sign in"></td>
          </tr>
        </table>
      </form>
    </div>
    <script type="text/javascript">
      function hideParent($this)
      {
        return $this.parentNode.style.display = 'none';
      }
    </script>
  </body>
</html>
