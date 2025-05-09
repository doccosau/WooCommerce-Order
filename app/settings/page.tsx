"use client"

import { useState } from "react"
import { Key, Globe, UserCog, Database, Save } from "lucide-react"
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from "@/components/ui/card"
import { Button } from "@/components/ui/button"
import { Badge } from "@/components/ui/badge"
import { Input } from "@/components/ui/input"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs"
import { Switch } from "@/components/ui/switch"
import { Label } from "@/components/ui/label"
import { Sidebar } from "@/components/sidebar"
import { TopNav } from "@/components/top-nav"

export default function SettingsPage() {
  const [selectedSite, setSelectedSite] = useState("all")

  return (
    <div className="flex min-h-screen bg-gray-50 dark:bg-gray-900">
      {/* Sidebar */}
      <Sidebar activePage="settings" />

      {/* Main content */}
      <div className="flex-1 flex flex-col">
        {/* Top navigation */}
        <TopNav selectedSite={selectedSite} setSelectedSite={setSelectedSite} />

        {/* Page content */}
        <main className="flex-1 overflow-y-auto bg-gray-50 dark:bg-gray-900 p-4 sm:p-6 lg:p-8">
          <div className="flex justify-between items-center mb-6">
            <h1 className="text-2xl font-semibold text-gray-900 dark:text-white">Cài đặt hệ thống</h1>
          </div>

          <Tabs defaultValue="api" className="w-full">
            <TabsList className="mb-4">
              <TabsTrigger value="api">
                <Key className="h-4 w-4 mr-2" />
                API & Kết nối
              </TabsTrigger>
              <TabsTrigger value="sites">
                <Globe className="h-4 w-4 mr-2" />
                Website
              </TabsTrigger>
              <TabsTrigger value="users">
                <UserCog className="h-4 w-4 mr-2" />
                Người dùng
              </TabsTrigger>
              <TabsTrigger value="backup">
                <Database className="h-4 w-4 mr-2" />
                Sao lưu
              </TabsTrigger>
            </TabsList>

            <TabsContent value="api">
              <div className="grid gap-6">
                <Card>
                  <CardHeader>
                    <CardTitle>Cài đặt API WooCommerce</CardTitle>
                    <CardDescription>Cấu hình kết nối API với các website WooCommerce</CardDescription>
                  </CardHeader>
                  <CardContent className="space-y-4">
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                      <div className="space-y-2">
                        <Label htmlFor="consumer-key">Consumer Key</Label>
                        <Input id="consumer-key" placeholder="ck_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx" />
                      </div>
                      <div className="space-y-2">
                        <Label htmlFor="consumer-secret">Consumer Secret</Label>
                        <Input id="consumer-secret" placeholder="cs_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx" />
                      </div>
                    </div>
                    <div className="space-y-2">
                      <Label htmlFor="api-url">URL API</Label>
                      <Input id="api-url" placeholder="https://example.com/wp-json/wc/v3" />
                    </div>
                    <div className="flex items-center space-x-2">
                      <Switch id="api-debug" />
                      <Label htmlFor="api-debug">Bật chế độ debug API</Label>
                    </div>
                  </CardContent>
                  <CardFooter>
                    <Button>
                      <Save className="h-4 w-4 mr-2" />
                      Lưu cài đặt
                    </Button>
                  </CardFooter>
                </Card>

                <Card>
                  <CardHeader>
                    <CardTitle>Đồng bộ dữ liệu</CardTitle>
                    <CardDescription>Cấu hình thời gian và phương thức đồng bộ dữ liệu</CardDescription>
                  </CardHeader>
                  <CardContent className="space-y-4">
                    <div className="space-y-2">
                      <Label htmlFor="sync-interval">Thời gian đồng bộ</Label>
                      <Select defaultValue="hourly">
                        <SelectTrigger id="sync-interval">
                          <SelectValue placeholder="Chọn thời gian" />
                        </SelectTrigger>
                        <SelectContent>
                          <SelectItem value="hourly">Mỗi giờ</SelectItem>
                          <SelectItem value="twicedaily">Hai lần mỗi ngày</SelectItem>
                          <SelectItem value="daily">Mỗi ngày</SelectItem>
                          <SelectItem value="weekly">Mỗi tuần</SelectItem>
                        </SelectContent>
                      </Select>
                    </div>
                    <div className="space-y-2">
                      <Label>Dữ liệu đồng bộ</Label>
                      <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div className="flex items-center space-x-2">
                          <Switch id="sync-orders" defaultChecked />
                          <Label htmlFor="sync-orders">Đơn hàng</Label>
                        </div>
                        <div className="flex items-center space-x-2">
                          <Switch id="sync-products" defaultChecked />
                          <Label htmlFor="sync-products">Sản phẩm</Label>
                        </div>
                        <div className="flex items-center space-x-2">
                          <Switch id="sync-customers" defaultChecked />
                          <Label htmlFor="sync-customers">Khách hàng</Label>
                        </div>
                      </div>
                    </div>
                  </CardContent>
                  <CardFooter>
                    <Button>
                      <Save className="h-4 w-4 mr-2" />
                      Lưu cài đặt
                    </Button>
                  </CardFooter>
                </Card>

                <Card>
                  <CardHeader>
                    <CardTitle>Tích hợp TikTok Shop</CardTitle>
                    <CardDescription>Cấu hình kết nối với TikTok Shop</CardDescription>
                  </CardHeader>
                  <CardContent className="space-y-4">
                    <div className="space-y-2">
                      <Label htmlFor="tiktok-app-id">App ID</Label>
                      <Input id="tiktok-app-id" placeholder="Nhập App ID TikTok Shop" />
                    </div>
                    <div className="space-y-2">
                      <Label htmlFor="tiktok-app-secret">App Secret</Label>
                      <Input id="tiktok-app-secret" placeholder="Nhập App Secret TikTok Shop" />
                    </div>
                    <div className="flex items-center space-x-2">
                      <Switch id="tiktok-enable" />
                      <Label htmlFor="tiktok-enable">Bật tích hợp TikTok Shop</Label>
                    </div>
                  </CardContent>
                  <CardFooter>
                    <Button>
                      <Save className="h-4 w-4 mr-2" />
                      Lưu cài đặt
                    </Button>
                  </CardFooter>
                </Card>
              </div>
            </TabsContent>

            <TabsContent value="sites">
              <div className="grid gap-6">
                <Card>
                  <CardHeader>
                    <CardTitle>Quản lý website</CardTitle>
                    <CardDescription>Thêm và quản lý các website WooCommerce</CardDescription>
                  </CardHeader>
                  <CardContent>
                    <div className="overflow-x-auto">
                      <table className="w-full text-sm text-left">
                        <thead className="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-800 dark:text-gray-400">
                          <tr>
                            <th scope="col" className="px-6 py-3">
                              Tên website
                            </th>
                            <th scope="col" className="px-6 py-3">
                              URL
                            </th>
                            <th scope="col" className="px-6 py-3">
                              Trạng thái
                            </th>
                            <th scope="col" className="px-6 py-3">
                              Đồng bộ lần cuối
                            </th>
                            <th scope="col" className="px-6 py-3">
                              Thao tác
                            </th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr className="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                            <td className="px-6 py-4 font-medium">Shop Thời Trang</td>
                            <td className="px-6 py-4">https://fashion.example.com</td>
                            <td className="px-6 py-4">
                              <Badge className="bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                                Kết nối
                              </Badge>
                            </td>
                            <td className="px-6 py-4">15/05/2023 15:30</td>
                            <td className="px-6 py-4">
                              <Button variant="ghost" size="sm">
                                Chỉnh sửa
                              </Button>
                            </td>
                          </tr>
                          <tr className="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                            <td className="px-6 py-4 font-medium">Shop Điện Tử</td>
                            <td className="px-6 py-4">https://electronics.example.com</td>
                            <td className="px-6 py-4">
                              <Badge className="bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                                Kết nối
                              </Badge>
                            </td>
                            <td className="px-6 py-4">15/05/2023 15:30</td>
                            <td className="px-6 py-4">
                              <Button variant="ghost" size="sm">
                                Chỉnh sửa
                              </Button>
                            </td>
                          </tr>
                          <tr className="bg-white dark:bg-gray-800">
                            <td className="px-6 py-4 font-medium">Shop Mỹ Phẩm</td>
                            <td className="px-6 py-4">https://cosmetics.example.com</td>
                            <td className="px-6 py-4">
                              <Badge className="bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                                Kết nối
                              </Badge>
                            </td>
                            <td className="px-6 py-4">15/05/2023 15:30</td>
                            <td className="px-6 py-4">
                              <Button variant="ghost" size="sm">
                                Chỉnh sửa
                              </Button>
                            </td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                  </CardContent>
                  <CardFooter>
                    <Button>Thêm website mới</Button>
                  </CardFooter>
                </Card>
              </div>
            </TabsContent>

            <TabsContent value="users">
              <div className="grid gap-6">
                <Card>
                  <CardHeader>
                    <CardTitle>Quản lý người dùng</CardTitle>
                    <CardDescription>Thêm và quản lý người dùng hệ thống</CardDescription>
                  </CardHeader>
                  <CardContent>
                    <div className="overflow-x-auto">
                      <table className="w-full text-sm text-left">
                        <thead className="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-800 dark:text-gray-400">
                          <tr>
                            <th scope="col" className="px-6 py-3">
                              Tên người dùng
                            </th>
                            <th scope="col" className="px-6 py-3">
                              Email
                            </th>
                            <th scope="col" className="px-6 py-3">
                              Vai trò
                            </th>
                            <th scope="col" className="px-6 py-3">
                              Trạng thái
                            </th>
                            <th scope="col" className="px-6 py-3">
                              Thao tác
                            </th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr className="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                            <td className="px-6 py-4 font-medium">Admin</td>
                            <td className="px-6 py-4">admin@example.com</td>
                            <td className="px-6 py-4">Quản trị viên</td>
                            <td className="px-6 py-4">
                              <Badge className="bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                                Hoạt động
                              </Badge>
                            </td>
                            <td className="px-6 py-4">
                              <Button variant="ghost" size="sm">
                                Chỉnh sửa
                              </Button>
                            </td>
                          </tr>
                          <tr className="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                            <td className="px-6 py-4 font-medium">Nhân viên 1</td>
                            <td className="px-6 py-4">staff1@example.com</td>
                            <td className="px-6 py-4">Nhân viên</td>
                            <td className="px-6 py-4">
                              <Badge className="bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                                Hoạt động
                              </Badge>
                            </td>
                            <td className="px-6 py-4">
                              <Button variant="ghost" size="sm">
                                Chỉnh sửa
                              </Button>
                            </td>
                          </tr>
                          <tr className="bg-white dark:bg-gray-800">
                            <td className="px-6 py-4 font-medium">Nhân viên 2</td>
                            <td className="px-6 py-4">staff2@example.com</td>
                            <td className="px-6 py-4">Nhân viên</td>
                            <td className="px-6 py-4">
                              <Badge className="bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300">
                                Vô hiệu
                              </Badge>
                            </td>
                            <td className="px-6 py-4">
                              <Button variant="ghost" size="sm">
                                Chỉnh sửa
                              </Button>
                            </td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                  </CardContent>
                  <CardFooter>
                    <Button>Thêm người dùng</Button>
                  </CardFooter>
                </Card>

                <Card>
                  <CardHeader>
                    <CardTitle>Phân quyền</CardTitle>
                    <CardDescription>Quản lý vai trò và quyền hạn người dùng</CardDescription>
                  </CardHeader>
                  <CardContent className="space-y-4">
                    <div className="space-y-2">
                      <Label htmlFor="role-select">Vai trò</Label>
                      <Select defaultValue="admin">
                        <SelectTrigger id="role-select">
                          <SelectValue placeholder="Chọn vai trò" />
                        </SelectTrigger>
                        <SelectContent>
                          <SelectItem value="admin">Quản trị viên</SelectItem>
                          <SelectItem value="staff">Nhân viên</SelectItem>
                        </SelectContent>
                      </Select>
                    </div>

                    <div className="space-y-2">
                      <Label>Quyền hạn</Label>
                      <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div className="space-y-2">
                          <div className="flex items-center space-x-2">
                            <Switch id="perm-dashboard" defaultChecked />
                            <Label htmlFor="perm-dashboard">Xem Dashboard</Label>
                          </div>
                          <div className="flex items-center space-x-2">
                            <Switch id="perm-orders" defaultChecked />
                            <Label htmlFor="perm-orders">Quản lý đơn hàng</Label>
                          </div>
                          <div className="flex items-center space-x-2">
                            <Switch id="perm-products" defaultChecked />
                            <Label htmlFor="perm-products">Quản lý sản phẩm</Label>
                          </div>
                          <div className="flex items-center space-x-2">
                            <Switch id="perm-customers" defaultChecked />
                            <Label htmlFor="perm-customers">Quản lý khách hàng</Label>
                          </div>
                        </div>
                        <div className="space-y-2">
                          <div className="flex items-center space-x-2">
                            <Switch id="perm-reports" defaultChecked />
                            <Label htmlFor="perm-reports">Xem báo cáo</Label>
                          </div>
                          <div className="flex items-center space-x-2">
                            <Switch id="perm-settings" defaultChecked />
                            <Label htmlFor="perm-settings">Cài đặt hệ thống</Label>
                          </div>
                          <div className="flex items-center space-x-2">
                            <Switch id="perm-users" defaultChecked />
                            <Label htmlFor="perm-users">Quản lý người dùng</Label>
                          </div>
                          <div className="flex items-center space-x-2">
                            <Switch id="perm-api" defaultChecked />
                            <Label htmlFor="perm-api">Cài đặt API</Label>
                          </div>
                        </div>
                      </div>
                    </div>
                  </CardContent>
                  <CardFooter>
                    <Button>
                      <Save className="h-4 w-4 mr-2" />
                      Lưu cài đặt
                    </Button>
                  </CardFooter>
                </Card>
              </div>
            </TabsContent>

            <TabsContent value="backup">
              <div className="grid gap-6">
                <Card>
                  <CardHeader>
                    <CardTitle>Sao lưu dữ liệu</CardTitle>
                    <CardDescription>Cấu hình sao lưu tự động dữ liệu hệ thống</CardDescription>
                  </CardHeader>
                  <CardContent className="space-y-4">
                    <div className="space-y-2">
                      <Label htmlFor="backup-interval">Tần suất sao lưu</Label>
                      <Select defaultValue="daily">
                        <SelectTrigger id="backup-interval">
                          <SelectValue placeholder="Chọn tần suất" />
                        </SelectTrigger>
                        <SelectContent>
                          <SelectItem value="daily">Hàng ngày</SelectItem>
                          <SelectItem value="weekly">Hàng tuần</SelectItem>
                          <SelectItem value="monthly">Hàng tháng</SelectItem>
                        </SelectContent>
                      </Select>
                    </div>
                    <div className="space-y-2">
                      <Label>Dữ liệu sao lưu</Label>
                      <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div className="flex items-center space-x-2">
                          <Switch id="backup-orders" defaultChecked />
                          <Label htmlFor="backup-orders">Đơn hàng</Label>
                        </div>
                        <div className="flex items-center space-x-2">
                          <Switch id="backup-products" defaultChecked />
                          <Label htmlFor="backup-products">Sản phẩm</Label>
                        </div>
                        <div className="flex items-center space-x-2">
                          <Switch id="backup-customers" defaultChecked />
                          <Label htmlFor="backup-customers">Khách hàng</Label>
                        </div>
                      </div>
                    </div>
                    <div className="space-y-2">
                      <Label htmlFor="backup-email">Email nhận sao lưu</Label>
                      <Input id="backup-email" placeholder="admin@example.com" />
                    </div>
                    <div className="flex items-center space-x-2">
                      <Switch id="backup-auto" defaultChecked />
                      <Label htmlFor="backup-auto">Bật sao lưu tự động</Label>
                    </div>
                  </CardContent>
                  <CardFooter className="flex justify-between">
                    <Button>
                      <Save className="h-4 w-4 mr-2" />
                      Lưu cài đặt
                    </Button>
                    <Button variant="outline">
                      <Database className="h-4 w-4 mr-2" />
                      Sao lưu ngay
                    </Button>
                  </CardFooter>
                </Card>

                <Card>
                  <CardHeader>
                    <CardTitle>Lịch sử sao lưu</CardTitle>
                    <CardDescription>Danh sách các bản sao lưu gần đây</CardDescription>
                  </CardHeader>
                  <CardContent>
                    <div className="overflow-x-auto">
                      <table className="w-full text-sm text-left">
                        <thead className="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-800 dark:text-gray-400">
                          <tr>
                            <th scope="col" className="px-6 py-3">
                              Tên file
                            </th>
                            <th scope="col" className="px-6 py-3">
                              Kích thước
                            </th>
                            <th scope="col" className="px-6 py-3">
                              Ngày tạo
                            </th>
                            <th scope="col" className="px-6 py-3">
                              Loại
                            </th>
                            <th scope="col" className="px-6 py-3">
                              Thao tác
                            </th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr className="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                            <td className="px-6 py-4 font-medium">backup-20230515.zip</td>
                            <td className="px-6 py-4">2.5 MB</td>
                            <td className="px-6 py-4">15/05/2023</td>
                            <td className="px-6 py-4">Tự động</td>
                            <td className="px-6 py-4">
                              <Button variant="ghost" size="sm">
                                Tải xuống
                              </Button>
                            </td>
                          </tr>
                          <tr className="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                            <td className="px-6 py-4 font-medium">backup-20230514.zip</td>
                            <td className="px-6 py-4">2.4 MB</td>
                            <td className="px-6 py-4">14/05/2023</td>
                            <td className="px-6 py-4">Tự động</td>
                            <td className="px-6 py-4">
                              <Button variant="ghost" size="sm">
                                Tải xuống
                              </Button>
                            </td>
                          </tr>
                          <tr className="bg-white dark:bg-gray-800">
                            <td className="px-6 py-4 font-medium">backup-20230513.zip</td>
                            <td className="px-6 py-4">2.3 MB</td>
                            <td className="px-6 py-4">13/05/2023</td>
                            <td className="px-6 py-4">Thủ công</td>
                            <td className="px-6 py-4">
                              <Button variant="ghost" size="sm">
                                Tải xuống
                              </Button>
                            </td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                  </CardContent>
                </Card>
              </div>
            </TabsContent>
          </Tabs>
        </main>
      </div>
    </div>
  )
}
