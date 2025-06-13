<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Dashboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    html, body {
      height: 100%;
      margin: 0;
    }
    body {
      overflow: hidden;
      background-color: #1a472a;
    }
    h1{
      background-color: #1a472a !important;
      color: #ffffff !important;
      text-transform: uppercase !important;
      font-weight: 600 !important;
      font-size: 1.25rem !important;
    }
    @media (min-width: 640px) {
      html {
        font-size: 14px;
      }
    }
    @media (min-width: 1024px) {
      html {
        font-size: 15px;
      }
    }
  </style>
</head>
<body class="h-screen w-screen"> 
  <div class="flex h-full w-full">
    <!-- Officers on Duty -->
    <div class="w-1/2 h-full p-0">
      <div class=" flex justify-center items-center space-x-4 text-center">
        <img src="log.png" 
            alt="UDF Logo" 
            height="100"
            width="100">
        
      </div>
      <div class="bg-white h-full w-full rounded-none border-r-2 border-green-600 overflow-y-auto">
        <h1 class="text-xl font-semibold p-4 text-green-800 bg-green-100">Officers on Duty</h1>
        <table class="table-auto w-full text-sm border border-green-600" id="officersTable">
          <thead class="bg-green-600 text-white text-center">
            <tr>
              <th class="px-4 py-2 border border-green-600">ARMY NO</th>
              <th class="px-4 py-2 border border-green-600">RANK</th>
              <th class="px-4 py-2 border border-green-600">FULL NAMES</th>
              <th class="px-4 py-2 border border-green-600">APPT</th>
              <th class="px-4 py-2 border border-green-600">SHIFT</th>
              
            </tr>
          </thead>
          <tbody class="text-green-700 text-center">
            <?php
              include 'db_connection.php';

              // Get current date and hour
              $currentDate = date('Y-m-d');
              $currentHour = (int) date('H');

              // Determine shift based on current time
              if ($currentHour >= 6 && $currentHour < 18) {
                  $shift = 'Day';
              } else {
                  $shift = 'Night';
              }

              $sql = "SELECT 
                          s.army_no, 
                          s.rank, 
                          s.full_names, 
                          s.appt, 
                          o.shift
                      FROM officers_on_duty o
                      INNER JOIN staff s ON o.staff_id = s.id
                      WHERE o.duty_date = ? AND o.shift = ?
                      ORDER BY o.id DESC";

              $stmt = $conn->prepare($sql);
              $stmt->bind_param("ss", $currentDate, $shift);
              $stmt->execute();
              $result = $stmt->get_result();

              if ($result && $result->num_rows > 0) {
                  while ($row = $result->fetch_assoc()) {
                      echo '
                      <tr class="border border-green-600 block sm:table-row">
                        <td class="px-4 py-2 border border-green-600 uppercase block sm:table-cell text-lg text-center">
                          <span class="sm:hidden font-semibold">ARMY NO: </span>' . htmlspecialchars($row['army_no']) . '
                        </td>
                        <td class="px-4 py-2 border border-green-600 uppercase block sm:table-cell text-lg text-center">
                          <span class="sm:hidden font-semibold">RANK: </span>' . htmlspecialchars($row['rank']) . '
                        </td>
                        <td class="px-4 py-2 border border-green-600 block uppercase sm:table-cell text-lg text-center">
                          <span class="sm:hidden font-semibold">FULL NAMES: </span>' . htmlspecialchars($row['full_names']) . '
                        </td>
                        <td class="px-4 py-2 border border-green-600 uppercase block sm:table-cell text-lg text-center">
                          <span class="sm:hidden font-semibold">APPT: </span>' . htmlspecialchars($row['appt']) . '
                        </td>
                        <td class="px-4 py-2 border border-green-600 uppercase block sm:table-cell text-lg text-center">
                          <span class="sm:hidden font-semibold">SHIFT: </span>' . htmlspecialchars($row['shift']) . '
                        </td>
                      </tr>';
                  }
              } else {
                  echo '<tr><td colspan="5" class="px-4 py-2 text-gray-500 text-center">No officers on duty for this shift.</td></tr>';
              }

              $stmt->close();
              $conn->close();
              ?>

          </tbody>
        </table>


      </div>
    </div>

    <!-- News Uploads -->
    <div class="w-1/2 h-full p-0 flex flex-col overflow-y-auto">
        <!-- Messages -->
        <div class="bg-white h-1/2 w-full overflow-y-auto border-b-2 border-green-600">
          <h1 class="text-xl uppercase font-semibold p-4 text-green-800 bg-green-100">Command Messages</h1>
          <div class="space-y-4 px-4 pb-4">
          <?php
              include 'db_connection.php';

              // Get today's date (Y-m-d)
              $today = date('Y-m-d');

              // Query critical messages posted today
              $sql = "SELECT title, content, created_at 
                      FROM news_uploads 
                      WHERE message_type = 'Critical' 
                        AND DATE(created_at) = ? 
                      ORDER BY created_at DESC";

              $stmt = $conn->prepare($sql);
              $stmt->bind_param("s", $today);
              $stmt->execute();
              $result = $stmt->get_result();

              if ($result && $result->num_rows > 0) {
                  while ($row = $result->fetch_assoc()) {
                      $postedTime = new DateTime($row['created_at']);
                      $currentTime = new DateTime();
                      $interval = $currentTime->diff($postedTime);
                      $timeAgo = $interval->y ? $interval->y.'y' :
                                ($interval->m ? $interval->m.'mo' :
                                ($interval->d ? $interval->d.'d' :
                                ($interval->h ? $interval->h.'h' :
                                ($interval->i ? $interval->i.'m' : $interval->s.'s'))));
                      echo '
                      <div class="w-full px- py-2">
                        <div class="w-full bg-green-100 text-green-900 p-4 rounded-lg shadow-sm">
                          <h3 class="font-semibold mb-1 uppercase">' . htmlspecialchars($row['title']) . '</h3>
                          <p class="text-sm mb-2">' . htmlspecialchars($row['content']) . '</p>
                          <div class="text-xs text-green-700 text-right">Posted ' . $timeAgo . ' ago</div>
                        </div>
                      </div>';
                  }
              } else {
                  // No critical message today - embed YouTube video
                  echo '
                  <div class="aspect-w-16 aspect-h-9 w-full  mx-auto">
                      <iframe 
                        src="https://www.youtube.com/embed/C30yZ3_vJPc?autoplay=1&mute=1" 
                        title="YouTube video"
                        frameborder="0" 
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
                        allowfullscreen 
                        width="760" height="315">
                      </iframe>
                    </div>

                    ';
              }

              $conn->close();
              ?>


          </div>
        </div>

        <!-- News Uploads -->
        <div class="bg-white h-1/2 w-full overflow-y-auto border-t-2 border-green-600">
          <h1 class="text-xl uppercase font-semibold p-4 text-green-800 bg-green-100">Updates</h1>
          <div class="space-y-4 px-4 pb-4">
            <?php
              include 'db_connection.php';
              $sql = "SELECT title, content, created_at FROM news_uploads WHERE message_type = 'Non-Critical' ORDER BY created_at DESC";

              $result = $conn->query($sql);
              if ($result && $result->num_rows > 0) {
                  while ($row = $result->fetch_assoc()) {
                      $postedTime = new DateTime($row['created_at']);
                      $currentTime = new DateTime();
                      $interval = $currentTime->diff($postedTime);
                      $timeAgo = $interval->y ? $interval->y.'y' :
                                ($interval->m ? $interval->m.'mo' :
                                ($interval->d ? $interval->d.'d' :
                                ($interval->h ? $interval->h.'h' :
                                ($interval->i ? $interval->i.'m' : $interval->s.'s'))));
                      echo '
                      <div class="bg-white p-4 border-l-4 border-green-600 shadow-sm">
                        <h3 class="font-medium text-green-800 uppercase">' . htmlspecialchars($row['title']) . '</h3>
                        <p class="text-green-600 text-sm">' . htmlspecialchars($row['content']) . '</p>
                        <div class="mt-2 text-xs text-green-600">' . $timeAgo . '</div>
                      </div>';
                  }
              } else {
                  echo '<div class="p-4 text-green-600">No news items found.</div>';
              }
              $conn->close();
            ?>
          </div>
        </div>
      </div>
  </div>
  

  <!-- Scripts -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script>
    
$(document).ready(function() {
  setInterval(function() {
    location.reload();
    console.log('Reload')
  }, 30000); // 30000 milliseconds = 30 seconds
});

    
  </script>
</body>
</html>
