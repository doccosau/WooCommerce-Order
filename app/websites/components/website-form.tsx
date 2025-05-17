"use client"

import { useState } from "react"
import { useRouter } from "next/navigation"
import { createWebsite, updateWebsite, testWooCommerceConnection, type WebsiteFormData } from "../actions"
import type { Website } from "@/types/supabase"
import { Button } from "@/components/ui/button"
import { Form, FormControl, FormDescription, FormField, FormItem, FormLabel, FormMessage } from "@/components/ui/form"
import { Input } from "@/components/ui/input"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"
import { Card, CardContent } from "@/components/ui/card"
import { toast } from "@/components/ui/use-toast"
import { useForm } from "react-hook-form"
import { zodResolver } from "@hookform/resolvers/zod"
import { z } from "zod"
import { ArrowLeft, Globe, Key, RefreshCw } from "lucide-react"
import Link from "next/link"

// Schema validation cho form
const formSchema = z.object({
  id: z.string().optional(),
  name: z.string().min(1, "Tên website không được để trống"),
  url: z.string().url("URL không hợp lệ").min(1, "URL không được để trống"),
  consumer_key: z.string().min(1, "Consumer key không được để trống"),
  consumer_secret: z.string().min(1, "Consumer secret không được để trống"),
  status: z.enum(["active", "inactive", "pending"]).default("active"),
})

interface WebsiteFormProps {
  website?: Website
}

export function WebsiteForm({ website }: WebsiteFormProps) {
  const router = useRouter()
  const [isSubmitting, setIsSubmitting] = useState(false)
  const [isTesting, setIsTesting] = useState(false)

  // Khởi tạo form
  const form = useForm<WebsiteFormData>({
    resolver: zodResolver(formSchema),
    defaultValues: website
      ? {
          id: website.id,
          name: website.name,
          url: website.url,
          consumer_key: website.consumer_key,
          consumer_secret: website.consumer_secret,
          status: website.status as "active" | "inactive" | "pending",
        }
      : {
          name: "",
          url: "",
          consumer_key: "",
          consumer_secret: "",
          status: "active",
        },
  })

  // Xử lý submit form
  const onSubmit = async (data: WebsiteFormData) => {
    setIsSubmitting(true)

    try {
      // Tạo FormData để gửi đi
      const formData = new FormData()

      if (website?.id) {
        formData.append("id", website.id)
      }

      formData.append("name", data.name)
      formData.append("url", data.url)
      formData.append("consumer_key", data.consumer_key)
      formData.append("consumer_secret", data.consumer_secret)
      formData.append("status", data.status)

      // Gọi action tương ứng
      const result = website ? await updateWebsite(formData) : await createWebsite(formData)

      if (!result.success) {
        // Hiển thị lỗi
        const errors = result.errors || {}

        // Hiển thị lỗi server nếu có
        if (errors.server) {
          toast({
            title: "Lỗi",
            description: errors.server[0],
            variant: "destructive",
          })
        }

        // Set lỗi vào form
        Object.keys(errors).forEach((key) => {
          if (key !== "server") {
            form.setError(key as any, {
              type: "manual",
              message: errors[key][0],
            })
          }
        })
      }
    } catch (error) {
      toast({
        title: "Lỗi",
        description: error instanceof Error ? error.message : "Có lỗi xảy ra khi lưu website",
        variant: "destructive",
      })
    } finally {
      setIsSubmitting(false)
    }
  }

  // Kiểm tra kết nối WooCommerce
  const testConnection = async () => {
    setIsTesting(true)

    try {
      // Lấy giá trị hiện tại từ form
      const url = form.getValues("url")
      const consumer_key = form.getValues("consumer_key")
      const consumer_secret = form.getValues("consumer_secret")

      // Validate trước khi test
      if (!url || !consumer_key || !consumer_secret) {
        toast({
          title: "Thiếu thông tin",
          description: "Vui lòng nhập đầy đủ URL, Consumer Key và Consumer Secret để kiểm tra kết nối.",
          variant: "destructive",
        })
        return
      }

      // Tạo FormData để gửi đi
      const formData = new FormData()
      formData.append("url", url)
      formData.append("consumer_key", consumer_key)
      formData.append("consumer_secret", consumer_secret)

      // Gọi action test kết nối
      const result = await testWooCommerceConnection(formData)

      if (result.success) {
        toast({
          title: "Kết nối thành công",
          description: "Kết nối đến WooCommerce API thành công.",
        })
      } else {
        throw new Error(result.error || "Không thể kết nối đến WooCommerce API")
      }
    } catch (error) {
      toast({
        title: "Lỗi kết nối",
        description: error instanceof Error ? error.message : "Có lỗi xảy ra khi kiểm tra kết nối",
        variant: "destructive",
      })
    } finally {
      setIsTesting(false)
    }
  }

  return (
    <Card>
      <CardContent className="pt-6">
        <Form {...form}>
          <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-6">
            <FormField
              control={form.control}
              name="name"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Tên Website</FormLabel>
                  <FormControl>
                    <Input placeholder="Nhập tên website" {...field} />
                  </FormControl>
                  <FormDescription>Tên hiển thị của website trong hệ thống</FormDescription>
                  <FormMessage />
                </FormItem>
              )}
            />

            <FormField
              control={form.control}
              name="url"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>URL Website</FormLabel>
                  <FormControl>
                    <div className="flex">
                      <Input placeholder="https://example.com" {...field} className="rounded-r-none" />
                      <Button
                        type="button"
                        variant="secondary"
                        className="rounded-l-none"
                        onClick={() => {
                          const url = field.value
                          if (url) {
                            window.open(url, "_blank")
                          }
                        }}
                      >
                        <Globe className="h-4 w-4" />
                      </Button>
                    </div>
                  </FormControl>
                  <FormDescription>URL đầy đủ của website WooCommerce</FormDescription>
                  <FormMessage />
                </FormItem>
              )}
            />

            <div className="grid gap-6 md:grid-cols-2">
              <FormField
                control={form.control}
                name="consumer_key"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>Consumer Key</FormLabel>
                    <FormControl>
                      <Input placeholder="ck_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx" {...field} />
                    </FormControl>
                    <FormDescription>WooCommerce API Consumer Key</FormDescription>
                    <FormMessage />
                  </FormItem>
                )}
              />

              <FormField
                control={form.control}
                name="consumer_secret"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>Consumer Secret</FormLabel>
                    <FormControl>
                      <Input placeholder="cs_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx" {...field} />
                    </FormControl>
                    <FormDescription>WooCommerce API Consumer Secret</FormDescription>
                    <FormMessage />
                  </FormItem>
                )}
              />
            </div>

            <FormField
              control={form.control}
              name="status"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Trạng thái</FormLabel>
                  <Select onValueChange={field.onChange} defaultValue={field.value}>
                    <FormControl>
                      <SelectTrigger>
                        <SelectValue placeholder="Chọn trạng thái" />
                      </SelectTrigger>
                    </FormControl>
                    <SelectContent>
                      <SelectItem value="active">Hoạt động</SelectItem>
                      <SelectItem value="inactive">Không hoạt động</SelectItem>
                      <SelectItem value="pending">Đang xử lý</SelectItem>
                    </SelectContent>
                  </Select>
                  <FormDescription>Trạng thái hoạt động của website</FormDescription>
                  <FormMessage />
                </FormItem>
              )}
            />

            <div className="flex flex-col sm:flex-row gap-3 pt-2">
              <Button type="button" variant="outline" onClick={testConnection} disabled={isSubmitting || isTesting}>
                {isTesting ? (
                  <>
                    <RefreshCw className="mr-2 h-4 w-4 animate-spin" />
                    Đang kiểm tra...
                  </>
                ) : (
                  <>
                    <Key className="mr-2 h-4 w-4" />
                    Kiểm tra kết nối
                  </>
                )}
              </Button>

              <div className="flex-1 flex justify-end gap-3">
                <Button type="button" variant="outline" asChild>
                  <Link href="/websites">
                    <ArrowLeft className="mr-2 h-4 w-4" />
                    Quay lại
                  </Link>
                </Button>

                <Button type="submit" disabled={isSubmitting}>
                  {isSubmitting ? (
                    <>
                      <RefreshCw className="mr-2 h-4 w-4 animate-spin" />
                      Đang lưu...
                    </>
                  ) : website ? (
                    "Cập nhật"
                  ) : (
                    "Thêm website"
                  )}
                </Button>
              </div>
            </div>
          </form>
        </Form>
      </CardContent>
    </Card>
  )
}
