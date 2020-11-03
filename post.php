<?php include "includes/header.php"; ?>
<!-- Navigation -->
<?php include "includes/nav.php"; ?>

<?php
if (isset($_POST['liked'])) {

  $post_id = $_POST['post_id'];
  $user_id = $_POST['user_id'];

  $query = "SELECT * FROM posztok WHERE post_id=$post_id";
  $postResult = mysqli_query($connect, $query);
  $post = mysqli_fetch_array($postResult);
  $likes = $post['likes'];



  mysqli_query($connect, "UPDATE posztok SET likes=$likes+1 WHERE post_id=$post_id");

  mysqli_query($connect, "INSERT INTO likes(user_id, post_id) VALUES($user_id, $post_id)");
  exit();
}

if (isset($_POST['unliked'])) {

  $post_id = $_POST['post_id'];
  $user_id = $_POST['user_id'];

  $query = "SELECT * FROM posztok WHERE post_id=$post_id";
  $postResult = mysqli_query($connect, $query);
  $post = mysqli_fetch_array($postResult);
  $likes = $post['likes'];

  //DELETE

  mysqli_query($connect, "DELETE FROM likes WHERE post_id=$post_id AND user_id=$user_id");
  //DECREMENTING LIKES

  mysqli_query($connect, "UPDATE posztok SET likes=$likes-1 WHERE post_id=$post_id");
  exit();
}



?>
<?php

if (isset($_GET['p_id'])) {
  $catch_post_id = $_GET['p_id'];

  $view_query = mysqli_prepare($connect, "UPDATE posztok SET post_views = post_views + 1 WHERE post_id = ?");

  mysqli_stmt_bind_param($view_query, "i", $catch_post_id);

  mysqli_stmt_execute($view_query);

  if (!$view_query) {

    die("adatbázis hiba!");
  }




  if (isset($_SESSION['username']) && is_admin($_SESSION['username'])) {
    $stmt1 = mysqli_prepare($connect, "SELECT post_cim, post_author, post_user, post_date, post_img, post_tartalom, post_views FROM posztok WHERE post_id = ?");
  } else {
    $stmt2 = mysqli_prepare($connect, "SELECT post_cim, post_author, post_user, post_date, post_img, post_tartalom, post_views FROM posztok WHERE post_id = ? AND post_status = ? ");

    $published = 'publikált';
  }

  if (isset($stmt1)) {

    mysqli_stmt_bind_param($stmt1, "i", $catch_post_id);

    mysqli_stmt_execute($stmt1);

    mysqli_stmt_bind_result($stmt1, $post_cim, $post_author, $post_user, $post_date, $post_img, $post_tartalom, $post_views);

    $stmt = $stmt1;
  } else {
    mysqli_stmt_bind_param($stmt2, "is", $catch_post_id, $published);

    mysqli_stmt_execute($stmt2);

    mysqli_stmt_bind_result($stmt2, $post_cim, $post_author, $post_user, $post_date, $post_img, $post_tartalom, $post_views);

    $stmt = $stmt2;
  }

  while (mysqli_stmt_fetch($stmt)) {

?>

    <!-- PAGE HEADER -->
    <div id="post-header" class="page-header">
      <div class="page-header-bg" style="background-image: url('/cms/img/<?php echo imagePlaceholder($post_img); ?>');" data-stellar-background-ratio="0.5"></div>
      <div class="container">
        <div class="row">
          <div class="col-md-6 title-bg">

            <h1>
              <?php echo $post_cim ?>
            </h1>
            <ul class="post-meta">
              <li><?php if (!empty($post_user)) : ?>
                  Szerző: <a href="/cms/user_post.php?user=<?php echo $post_user; ?>&p_id=<?php echo $catch_post_id; ?>" target="_blank"><?php echo $post_user ?></a>
                <?php elseif (!empty($post_author)) : ?>
                  Szerző: <a href="/cms/author_post.php?author=<?php echo $post_author; ?>&p_id=<?php echo $catch_post_id; ?>" target="_blank"><?php echo $post_author ?></a>
                <?php endif; ?>
              </li>
              <li><span class="fa fa-clock-o"></span> <?php echo $post_date ?></li>
              <li><span class="fa fa-eye"></span> <?php echo $post_views; ?></li>
            </ul>
          </div>
        </div>
      </div>
    </div>

    <!-- Page Content -->
    <div class="container">

      <div class="row">

        <!-- Blog Entries Column -->
        <div class="col-md-8">

          <!-- Blog Post -->
          <div class="section-row">
            <div class="post-share">
              <div id="fb-root"></div>
              <script async defer crossorigin="anonymous" src="https://connect.facebook.net/hu_HU/sdk.js#xfbml=1&version=v6.0"></script>
              <div class="fb-share-button" data-href="https://www.msztesz.hu/cms/post.php?p_id=<?php echo $catch_post_id; ?>" data-layout="button_count" data-size="large"><a target="_blank" href="https://www.facebook.com/sharer/sharer.php?u=https%3A%2F%2Fdevelopers.facebook.com%2Fdocs%2Fplugins%2F&amp;src=sdkpreparse" class="fb-xfbml-parse-ignore">Megosztás</a></div>
              <a href="#" data-toggle="modal" data-target="#emailmodal"><i class="fa fa-envelope"></i><span>Üzenjen nekünk!</span></a>
            </div>
          </div>
          <p><?php echo $post_tartalom ?></p>

          <?php mysqli_stmt_free_result($stmt); ?>
          <div class="pull-left liked">Likes: (<?php getLiked($catch_post_id); ?>)</div>
          <div class="clearfix"></div>
          <!-- Comments Form -->

          <div class="row">
            <div class="col-md-12 col-sm-12">
              <div class="comment-wrapper">
                <div class="panel panel-info">

                  <?php if (isLoggedIn()) : ?>

                    <p class="pull-right"><a class="<?php echo userLiked($catch_post_id) ? 'unlike' : 'like' ?>" href=""><span><i class="fas fa-thumbs-up"></i></span> <?php echo userLiked($catch_post_id) ? 'Unlike' : 'Like' ?></a></p>

                    <form action="" method="post" role="form">
                      <div class="form-group">
                        <div class="input-group mb-3">
                          <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fa fa-user"></i></span>
                          </div>
                          <input class="form-control" type="text" name="comment_author" value="<?php echo $_SESSION['username']; ?>" disabled>
                        </div>
                      </div>
                      <div class="panel-body">
                        <textarea name="comment_content" class="form-control" placeholder="írj egy kommentet.." rows="3"></textarea>
                        <br>
                        <button type="submit" name="create_comment" id="comment" class="btn comm pull-right">Küldés</button>
                    </form>


                  <?php else : ?>
                    <div class="ilogin">A Hozzászóláshoz és likehoz bejelentkezés szükséges.</div>
                    <div class="needlogin"><a class="login-like left" href="#" data-target="#login" data-toggle="modal"><i class="fa fa-user-plus"></i> Bejelentkezés</a></div>


                  <?php endif ?>



                <?php } ?>


                <!-- Comment -->

                <?php

                if (isset($_POST['create_comment'])) {

                  $catch_post_id = $_GET['p_id'];
                  $comment_author = $_POST['comment_author'];
                  $comment_content = $_POST['comment_content'];

                  date_default_timezone_set("Europe/Budapest");
                  $currentTime = time();
                  $comment_date = strftime("%Y.%m.%d %H:%M", $currentTime);

                  if (!empty($comment_author) && !empty($comment_content)) {

                    $query = "INSERT INTO comments (comment_post_id, comment_author, comment_content, comment_status,comment_date)";

                    $query .= "VALUES ($catch_post_id ,'{$comment_author}', '{$comment_content}', 'elfogadva','{$comment_date}')";

                    $create_comment_query = mysqli_query($connect, $query);

                    conFirm($create_comment_query);
                  } else {
                    echo "<script>alert('Kérjük töltse ki a mezőket!')</script>";
                  }
                }

                ?>




                <div class="clearfix"></div>
                <h4 class="fieldinfo mb-4"> Kommentek <i class="fa fa-comment"></i></h4>
                <hr>

                <!-- posted Comment -->

                <?php
                $query = "SELECT * FROM comments WHERE comment_post_id = {$catch_post_id} ";
                $query .= "AND comment_status = 'elfogadva' ";
                $query .= "ORDER BY comment_id DESC LIMIT 5";
                $select_comment_query = mysqli_query($connect, $query);
                conFirm($select_comment_query);
                while ($row = mysqli_fetch_array($select_comment_query)) {
                  $comment_date   = $row['comment_date'];
                  $comment_content = $row['comment_content'];
                  $comment_author = $row['comment_author'];




                ?>


                  <ul class="media-list comments">
                    <li class="media">
                      <div class="media-body">
                        <span class="text-muted pull-right">
                          <small class="text-muted"> <?php echo $comment_date; ?></small>
                        </span>
                        <strong class="text-primary"><?php echo $comment_author; ?></strong>
                        <p>
                          <?php echo $comment_content; ?>
                        </p>
                      </div>
                    </li>
                  </ul>

              <?php }
              } else {

                header("Location: index.php");
              }
              ?>

                </div>
              </div>

            </div>

          </div>
        </div>


      </div>

      <!-- Blog Sidebar Widgets Column -->
      <!--?php include "includes/side.php"; ?-->
    </div>
    <!-- /.row -->
    </div>


    <?php include "includes/footer.php"; ?>
    <script>
      $(document).ready(function() {

        var user_id = <?php echo loggedInUserId(); ?>;
        var post_id = <?php echo $catch_post_id; ?>



        //LIKE

        $('.like').click(function() {
          $.ajax({
            url: "/cms/post.php?p_id=<?php echo $catch_post_id; ?>",
            type: 'post',
            data: {
              'liked': 1,
              'post_id': post_id,
              'user_id': user_id

            }
          });
        });

        // UNLIKE

        $('.unlike').click(function() {
          $.ajax({
            url: "/cms/post.php?p_id=<?php echo $catch_post_id; ?>",
            type: 'post',
            data: {
              'unliked': 1,
              'post_id': post_id,
              'user_id': user_id

            }
          });
        });

      })
    </script>