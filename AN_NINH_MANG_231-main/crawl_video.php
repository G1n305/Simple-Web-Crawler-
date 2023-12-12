<?php
//Audio downloader
function downloadVideo($url, $folderPath) {
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

    // Tìm kiếm tất cả các thẻ <a>
    $vidTags = $dom->getElementsByTagName('video');

    $downloadedVideo = []; // Mảng để lưu trữ URL của các tệp âm thanh tải xuống thành công

    // Tải xuống mỗi tệp âm thanh
    foreach ($vidTags as $vidTag) {
        $sourceTags = $vidTag->getElementsByTagName('source');
        foreach ($sourceTags as $sourceTag) {
            $vidUrl = $sourceTag->getAttribute('src');
            $vidUrl = urljoin($url, $vidUrl);
         } // Chuyển URL thành tuyệt đối
        echo "URL Audio: $vidUrl<br>";
        ob_flush();
        flush();

            // Tải tệp âm thanh
            $vidData = file_get_contents($vidUrl);
            if ($vidData === false) {
                echo "Failed to download audio from $vidUrl<br>";
                ob_flush();
                flush();
                continue;
            }

            $vidName = $folderPath . '/' . basename($vidUrl);
            if (file_put_contents($vidName, $vidData) === false) {
                echo "Failed to save audio $vidName<br>";
            } else {
                echo "Downloaded: $vidName<br>";
                $downloadedVideo[] = $vidUrl; // Lưu URL vào mảng
            }
            ob_flush();
            flush();
        }

    return $downloadedVideo; // Trả về mảng các URL đã tải xuống
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
 
if (isset($_GET['url'])) {
    $url = $_GET['url'];
    $folderPath = 'D:/database/htdocs/resulta'; // Thay thế bằng đường dẫn thư mục mong muốn
    $downloadedVideo = downloadVideo($url, $folderPath);

    echo "<p>Crawling data from: $url</p>";
    foreach ($downloadedVideo as $vidUrl) {
        echo "<p>Downloaded Audio URL: $vidUrl</p>";

    }
    ob_flush();
    flush();

} else {
    echo "No URL provided.<br>";
    ob_flush();
    flush();
}
?>