<?php
function downloadImages($url, $folderPath) {
     // Tạo context với User-Agent giả mạo
     $options = [
        'https' => [
            'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.36\r\n"
        ]
    ];
    $context = stream_context_create($options);

    // Lấy nội dung HTML từ URL sử dụng context đã tạo
    $html = file_get_contents($url, false, $context);
    if ($html === false) {
        echo "Failed to retrieve content from the URL: $url<br>";
        ob_flush();
        flush();
        return;
    }

    // Tạo đối tượng DOMDocument và tải nội dung HTML
    $dom = new DOMDocument;
    @$dom->loadHTML($html);

    // Tìm tất cả các thẻ ảnh
    $imgTags = $dom->getElementsByTagName('img');

    $downloadedImages = []; // Mảng để lưu trữ URL của các ảnh tải xuống thành công

    // Tải xuống từng ảnh
    foreach ($imgTags as $imgTag) {
        $imgUrl = $imgTag->getAttribute('src');
        $imgUrl = urljoin($url, $imgUrl); // Chuyển URL thành tuyệt đối
        echo "URL Ảnh: $imgUrl<br>";
        ob_flush();
        flush();

        // Tải ảnh
        $imgData = file_get_contents($imgUrl);
        if ($imgData === false) {
            echo "Failed to download image from $imgUrl<br>";
            ob_flush();
            flush();
            continue;
        }
        $imgName = $folderPath . '/' . basename($imgUrl);
        if (file_put_contents($imgName, $imgData) === false) {
            echo "Failed to save image $imgName<br>";
        } else {
            echo "Downloaded to: $imgName<br>";
            $downloadedImages[] = $imgUrl; // Lưu URL vào mảng
        }
        ob_flush();
        flush();
    }

    return $downloadedImages; // Trả về mảng các URL đã tải xuống
}

function urljoin($base, $relative) {
    $parts = parse_url($relative);

    if ($parts === false) {
        return false;
    }

    if (empty($parts['scheme'])) {
        $base_parts = parse_url($base);

        if ($base_parts === false) {
            return false;
        }

        $parts['scheme'] = $base_parts['scheme'];
        $parts['host'] = $base_parts['host'];

        if (empty($parts['path']) || $parts['path'][0] !== '/') {
            $basePath = explode('/', $base_parts['path']);
            array_pop($basePath);

            $parts['path'] = implode('/', $basePath) . '/' . $parts['path'];
        }
    }

    // Manually concatenate the URL components
    $result = $parts['scheme'] . '://';
    if (!empty($parts['user'])) {
        $result .= $parts['user'];
        if (!empty($parts['pass'])) {
            $result .= ':' . $parts['pass'];
        }
        $result .= '@';
    }
    $result .= $parts['host'];
    if (!empty($parts['port'])) {
        $result .= ':' . $parts['port'];
    }
    $result .= $parts['path'];
    if (!empty($parts['query'])) {
        $result .= '?' . $parts['query'];
    }
    if (!empty($parts['fragment'])) {
        $result .= '#' . $parts['fragment'];
    }

    return $result;
}
 
if (isset($_GET['url']) && isset($_GET['folderPath'])) {
    $url = $_GET['url'];
    $folderPath = $_GET['folderPath'];
        // Đảm bảo rằng đường dẫn thư mục tồn tại và có thể ghi vào
        if (!file_exists($folderPath)) {
            mkdir($folderPath, 0777, true); // Tạo thư mục nếu nó không tồn tại
        }
        //$folderPath = 'D:/database/htdocs/result'; // Thay thế bằng đường dẫn thư mục mong muốn
    $downloadedImages = downloadImages($url, $folderPath);

    echo "<p>Crawling data from: $url</p>";
    foreach ($downloadedImages as $imgUrl) {
        echo "<p>Downloaded Image URL: $imgUrl</p>";
    }
    ob_flush();
    flush();
} else {
    echo "No URL provided.<br>";
    ob_flush();
    flush();
}

?>
