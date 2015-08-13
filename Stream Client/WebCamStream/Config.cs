using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace WebCamStream
{
    public static class Config
    {
        public static int ALIVE_TICK = 1000;
        public static int DEFAULT_STREAM_SPEED = 300;
        public const string baseSrvUrl = "http://localhost/web-cam-stream";
        public const string userAgent = "Mozilla/5.0 (Windows NT 6.1; WOW64; Trident/7.0; AS; rv:11.0) like Gecko";
    }
}