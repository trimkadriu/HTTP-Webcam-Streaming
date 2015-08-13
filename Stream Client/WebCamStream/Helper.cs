using System;
using System.Collections.Generic;
using System.Drawing;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace WebCamStream
{
    static class Helper
    {
        public static string getUrl(string fileName)
        {
            return Config.baseSrvUrl + "/" + fileName + ".php";
        }

        public static byte[] convertImageToByteArray(Image image)
        {
            ImageConverter _imageConverter = new ImageConverter();
            byte[] xByte = (byte[])_imageConverter.ConvertTo(image, typeof(byte[]));
            return xByte;
        }

        public static String convertByteArrayToBase64(byte[] bytes)
        {
            return Convert.ToBase64String(bytes);
        }
    }
}
