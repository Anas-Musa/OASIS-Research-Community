import axios from "axios";

// export const baseURL = "https://oasisresearchcommunity.org/v1/";
// export const webURL = "https://oasisresearchcommunity.org/";
export const baseURL = "http://192.168.64.3/ORC/v1/";
export const webURL = "http://192.168.64.3/ORC/";

export const instance = axios.create({
  baseURL,
  timeout: 60000,
  headers: {
    "Content-type": "application/json",
  },
});
