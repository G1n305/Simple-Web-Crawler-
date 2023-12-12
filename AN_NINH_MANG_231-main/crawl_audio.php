<?php
//Audio downloader
function downloadAudio($url, $folderPath) {
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
    $audioTags = $dom->getElementsByTagName('audio');

    $downloadedAudios = []; // Mảng để lưu trữ URL của các tệp âm thanh tải xuống thành công

    // Tải xuống mỗi tệp âm thanh
    foreach ($audioTags as $audioTag) {
        $sourceTags = $audioTag->getElementsByTagName('source');
        foreach ($sourceTags as $sourceTag) {
            $audioUrl = $sourceTag->getAttribute('src');
            $audioUrl = urljoin($url, $audioUrl);
         } // Chuyển URL thành tuyệt đối
        echo "URL Audio: $audioUrl<br>";
        ob_flush();
        flush();

            // Tải tệp âm thanh
            $audioData = file_get_contents($audioUrl);
            if ($audioData === false) {
                echo "Failed to download audio from $audioUrl<br>";
                ob_flush();
                flush();
                continue;
            }

            $audioName = $folderPath . '/' . basename($audioUrl);
            if (file_put_contents($audioName, $audioData) === false) {
                echo "Failed to save audio $audioName<br>";
            } else {
                echo "Downloaded: $audioName<br>";
                $downloadedAudios[] = $audioUrl; // Lưu URL vào mảng
            }
            ob_flush();
            flush();
        }

    return $downloadedAudios; // Trả về mảng các URL đã tải xuống
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
    $downloadedAudio = downloadAudio($url, $folderPath);

    echo "<p>Crawling data from: $url</p>";
    foreach ($downloadedAudio as $audioUrl) {
        echo "<p>Downloaded Audio URL: $audioUrl</p>";

    }
    ob_flush();
    flush();

} else {
    echo "No URL provided.<br>";
    ob_flush();
    flush();
}
?>