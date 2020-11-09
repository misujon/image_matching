##Laravel Image Matching Web Application
<p>Application that works for matching images to provided website urls. The application get one image file,
multiple website urls and multiple tags that seperated by comma. On the action performs to match image 
checkes all the input values and encode the image to base64 hashing string. The takes grabs the data from 
the websites that takes from the input and filters the images and also checkes them if the images matched with 
the uploaded one. The images are going to related image if those doesn't matches the uploaded image hash. After
 checking all the conditions it structures the data and provide the result to view.</p>
 
## How it works
 - Run the application
 <img src="https://www.linkpicture.com/q/1_753.jpg"><br>
 - Input One image
 - Add websites url to search the image
 - Add tags
 - Click Search Matches
  <img src="https://www.linkpicture.com/q/2_519.jpg"><br>
 - Wait for search action complete and get the result.
 <img src="https://www.linkpicture.com/q/3_442.jpg">
 
### Files those contains works
 - **Controllers:** Search.php (Perform the image matching action)
 - **Routes:** /search
 - **Views:** Welcome (Contains the matching input form), search (Shows the matching results)

### How to install
 - Download or Clone the file to the local server directory just
 - Go to the local server url and that will works. *Example: http://localhost/your-directory*
 
<br/><br/>
<h2>Contribution</h2>

<a href="http://misujon.com/" target="_blank"><img width="150" src="http://www.misujon.com/wp-content/uploads/2017/11/Logo.png"></a>
<br>M.i.Sujon<br>
<a href="http://misujon.com/" target="_blank">www.misujon.com</a><br>
<a href="mailto:contact@misujon.com">contact@misujon.com</a>

<br>
<h2>Thank You</h2>
