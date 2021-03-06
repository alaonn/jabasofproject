    <?php
    //create_cat.php
    include 'connect.php';
    include 'header.php';
     
    echo '<h2>Crée un sujet de discussion</h2>';
    if($_SESSION['signed_in'] == false)
    {
        //the user is not signed in
        echo 'Désolé, vous devez être <a href="/forum/signin.php">connecté</a> pour créer un sujet.';
    }
    else
    {
        //the user is signed in
        if($_SERVER['REQUEST_METHOD'] != 'POST')
        {   
            //the form hasn't been posted yet, display it
            //retrieve the categories from the database for use in the dropdown
            $sql = "SELECT
                        cat_id,
                        cat_name,
                        cat_description
                    FROM
                        categories";
             
            $result = mysqli_query($link,$sql);
             
            if(!$result)
            {
                //the query failed, uh-oh :-(
                echo 'Erreur de base de donnée. Veuillez réessayer ultérieurement.';
            }
            else
            {
                if(mysqli_num_rows($result) == 0)
                {
                    //there are no categories, so a topic can't be posted
                    if($_SESSION['user_level'] == 1)
                    {
                        echo 'Vous n avez pas encore créer de catégorie.';
                    }
                    else
                    {
                        echo 'Avant de pouvoir créer un sujet, attendez qu un administrateur crée une catégorie.';
                    }
                }
                else
                {
             
                    echo '<form method="post" action="">
                        Subject: <input type="text" name="topic_subject" />
                        Catégorie:'; 
                     
                    echo '<select name="topic_cat">';
                        while($row = mysqli_fetch_assoc($result))
                        {
                            echo '<option value="' . $row['cat_id'] . '">' . $row['cat_name'] . '</option>';
                        }
                    echo '</select>'; 
                         
                    echo 'Message: <textarea name="post_content" /></textarea>
                        <input type="submit" value="Créer un sujet" />
                     </form>';
                }
            }
        }
        else
        {
            //start the transaction
            $query  = "BEGIN WORK;";
            $result = mysqli_query($link,$query);
             
            if(!$result)
            {
                //Damn! the query failed, quit
                echo 'Une erreur est survenu lors de la création du sujet. Veuillez réessayer ultérieurement.';
            }
            else
            {
         
                //the form has been posted, so save it
                //insert the topic into the topics table first, then we'll save the post into the posts table
                $sql = "INSERT INTO 
                            topics(topic_subject,
                                   topic_date,
                                   topic_cat,
                                   topic_by)
                       VALUES('" . mysqli_real_escape_string($link, $_POST['topic_subject']) . "',
                                   NOW(),
                                   " . mysqli_real_escape_string($link, $_POST['topic_cat']) . ",
                                   " . $_SESSION['user_id'] . "
                                   )";
                          
                $result = mysqli_query($sql);
                if(!$result)
                {
                    //something went wrong, display the error
                    echo 'Une erreur est survenu lors de l insertion de vos données. Veuillez réessayer ultérieurement.' . mysqli_error();
                    $sql = "ROLLBACK;";
                    $result = mysqli_query($sql);
                }
                else
                {
                    //the first query worked, now start the second, posts query
                    //retrieve the id of the freshly created topic for usage in the posts query
                    $topicid = mysqli_insert_id();
                     
                    $sql = "INSERT INTO
                                posts(post_content,
                                      post_date,
                                      post_topic,
                                      post_by)
                            VALUES
                                ('" . mysqli_real_escape_string($_POST['post_content']) . "',
                                      NOW(),
                                      " . $topicid . ",
                                      " . $_SESSION['user_id'] . "
                                )";
                    $result = mysqli_query($sql);
                     
                    if(!$result)
                    {
                        //something went wrong, display the error
                        echo 'Une erreur est survenu lors de l insertion de votre sujet. Veuillez réessayer ultérieurement.' . mysqli_error();
                        $sql = "ROLLBACK;";
                        $result = mysqli_query($sql);
                    }
                    else
                    {
                        $sql = "COMMIT;";
                        $result = mysqli_query($sql);
                         
                        //after a lot of work, the query succeeded!
                        echo 'Vous avez crée <a href="topic.php?id='. $topicid . '">,votre nouveau sujet</a>.';
                    }
                }
            }
        }
    }
     
    include 'footer.php';
    ?>